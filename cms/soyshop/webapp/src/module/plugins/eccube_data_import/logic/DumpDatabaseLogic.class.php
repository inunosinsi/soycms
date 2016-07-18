<?php
/**
 * EC CUBEのデータベースではSOY2DAOではなく、PDOを利用しています。DSNを都度切り替えるのが大変だから
 * EC CUBEからデータをインポートする時はGUIからのデータのインジェクションを行わないので、プリペアドステートメントは行っていません
 * メモリを抑えるためにDAOではなく、最低限のデータ量だけを取得するSQLで実行しています。
 */
class DumpDatabaseLogic extends SOY2LogicBase{
	
	const LIMIT = 100;
	
	private $pdo;
	
	//EC CUBEの頃のカテゴリIDを保存しておく array("oldId" => "newId")の配列を想定
	private $oldIds = array(null);
	
	//各カテゴリの親子関係を保持しておく array("oldId" => "level")の配列を想定 rootを0、下に行くほど+1
	private $relatives = array(null);
	private $parents = array();
	
	function DumpDatabaseLogic(){
		//ポイントプラグインのインストール
		if(!SOYShopPluginUtil::checkIsActive("common_point_base")){
			$logic = SOY2Logic::createInstance("logic.plugin.SOYShopPluginLogic");
		    $logic->prepare();
		    $logic->installModule("common_point_base");
		    unset($logic);
		    
		    //住所
		    SOY2::import("domain.config.SOYShop_Area");
		}
	}
	
	function execute(){
		set_time_limit(0);
		
		self::pdo();
		
		//PDOの接続に失敗した場合は処理を終了する
		if(!$this->pdo) return false;
				
		//顧客の登録
		self::registerCustomer();
		
		//カテゴリの登録
		self::regiserCategory();
		
		//商品の登録
		self::registerItem();
		
		//注文の登録
		self::registerOrder();
		
		//設定内容の削除
		SOY2::import("module.plugins.eccube_data_import.util.EccubeDataImportUtil");
		EccubeDataImportUtil::clearTable();
		
		return true;
	}
	
	private function registerCustomer(){
		$dao = SOY2DAOFactory::create("user.SOYShop_UserDAO");
		$logic = SOY2Logic::createInstance("module.plugins.common_point_base.logic.PointBaseLogic");
		
		$n = 1;
		$total = self::getTotal("dtb_customer");
		
		$dao->begin();
		do{
			$offset = self::LIMIT * ($n - 1) + 1;
			foreach($this->pdo->query(
				"SELECT customer_id, name01,name02,kana01,kana02,zip01,zip02,pref,addr01,addr02,email,tel01,tel02,tel03,fax01,fax02,fax03,sex,birth,password,note,point,status,create_date,update_date,del_flg,mailmaga_flg FROM dtb_customer ORDER BY customer_id ASC LIMIT " . self::LIMIT . " OFFSET " . $offset
			) as $r){
				if(strlen($r["email"]) === 0) continue;
				try{
					$res = $dao->executeQuery("SELECT id FROM soyshop_user WHERE mail_address = :email LIMIT 1", array(":email" => self::t($r["email"])));
					if(count($res) > 0) continue;
				}catch(Exception $e){
					//
				}
				$user = new SOYShop_User();
				$user->setName(self::t($r["name01"]). " " . self::t($r["name02"]));
				$user->setReading(self::t($r["kana01"]) . " " . self::t($r["kana01"]));
				$user->setZipCode(self::t($r["zip01"]).self::t($r["zip02"]));
				$user->setArea(self::t($r["pref"]));
				$user->setAddress1(self::t($r["addr01"]));
				$user->setAddress2(self::t($r["addr02"]));
				$user->setMailAddress(self::t($r["email"]));
				$user->setTelephoneNumber(self::t($r["tel01"]). self::t($r["tel02"]) . self::t($r["tel03"]));
				if(isset($r["fax01"])) $user->setFaxNumber(self::t($r["fax01"]). self::t($r["fax02"]) . self::t($r["fax03"]));
				if((int)$r["sex"] > 0) $user->setGender((int)$r["sex"] - 1);
				if(isset($r["birth"])) $user->setBirthday(self::t(substr($r["birth"], 0, strpos($r["birth"], " "))));
				$user->setMemo(self::t($r["note"]));
				$d = self::convertTimestamp(self::t($r["create_date"]));
				$user->setRegisterDate(self::t($d));
				$user->setRealRegisterDate(self::t($d));
				$user->setUpdateDate(self::convertTimestamp($r["update_date"]));
				
				//必ず入れる値 本登録ユーザにする
				$user->setUserType(SOYShop_User::USERTYPE_REGISTER);
				$user->setAttribute3("EC CUBE");
				
				//メルマガ 1と2が登録、3が拒否
				if((int)$r["mailmaga_flg"] === 3) $user->setNotSend(SOYShop_User::USER_NOT_SEND);
				
				//def_flgを見る
				if((int)$r["del_flg"] > 0){
					/**
					 * @ToDo ユーザの削除
					 */
				}
				
				try{
					$id = $dao->insert($user);
				}catch(Exception $e){
					//
				}
				
				//処理が重くなるけど、ここでパスワードをコピーする update時はセットした値がハッシュ化されないため
				$user->setId($id);
				$user->setPassword(self::t($r["password"]));
				try{
					$dao->update($user);
				}catch(Exception $e){
					//
				}
				
				if((int)$r["point"] > 0) $logic->insert($r["point"], "EC CUBEからの移行分", $id);
				
				//お届け先住所がある場合
				$address = array();
				foreach($this->pdo->query(
					"SELECT name01,name02,kana01,kana02,zip01,zip02,pref,addr01,addr02,tel01,tel02,tel03 FROM dtb_other_deliv WHERE customer_id = " . $r["customer_id"]
				) as $rr){
					$addr = array();
					$addr["office"] = self::t($rr["name01"]);
					$addr["name"] = self::t($rr["name02"]);
					$addr["reading"] = self::t($rr["kana02"]);
					$addr["zipCode"] = self::t($rr["zip01"]) . self::t($rr["zip02"]);
					$addr["area"] = (int)$rr["pref"];
					$addr["address1"] = self::t($rr["addr01"]);
					$addr["address2"] = self::t($rr["addr02"]);
					$addr["telephoneNumber"] = self::t($rr["tel01"]). self::t($rr["tel02"]) . self::t($rr["tel03"]);
					
					$address[] = $addr;
				}
				
				if(count($address)){
					$user->setAddressList($address);
					try{
						$dao->update($user);
					}catch(Exception $e){
						//
					}
				}
			}
		}while($total > self::LIMIT * $n++);
		$dao->commit();
		
		unset($user);
		unset($logic);
		unset($dao);
		
		unset($addr);
		unset($address);
		
		unset($r);
		unset($rr);
		unset($n);
		unset($total);
		unset($offset);
	}
	
	private function regiserCategory(){
		$exeFlag = false;
		$dao = SOY2DAOFactory::create("shop.SOYShop_CategoryDAO");
		
		$n = 1;
		$total = self::getTotal("dtb_category");
		
		$dao->begin();
		do{
			$offset = self::LIMIT * ($n - 1) + 1;
			foreach($this->pdo->query("SELECT category_id,category_name,parent_category_id,del_flg FROM dtb_category ORDER BY category_id ASC LIMIT " . self::LIMIT . " OFFSET " . $offset) as $r){
				try{
					$res = $dao->executeQuery("SELECT id FROM soyshop_category where category_name = :name LIMIT 1", array(":name" => self::t($r["category_name"])));
					if(count($res) > 0) continue;
				}catch(Exception $e){
					//
				}
				$category = new SOYShop_Category();
				$category->setName(self::t($r["category_name"]));
				$category->setAlias(self::t($r["category_name"]));
				$category->setIsOpen( ((int)$r["del_flg"] > 0) ? SOYShop_Category::NO_OPEN : SOYShop_Category::IS_OPEN );
				
				//親カテゴリがある場合
				if((int)$r["parent_category_id"] > 0 && isset($this->oldIds[$r["parent_category_id"]])){
					$category->setParent($this->oldIds[$r["parent_category_id"]]);
					$this->relatives[$r["category_id"]] = (int)$this->relatives[$r["parent_category_id"]] + 1;
				//親カテゴリがない場合
				}else{
					$this->relatives[$r["category_id"]] = 0;
				}
				
				try{
					//カテゴリを登録した際に、oldId(EC CUBE)とnewId(SOY Shop)を紐づける
					$this->oldIds[$r["category_id"]] = $dao->insert($category);
					$exeFlag = true;
				}catch(Exception $e){
					//
				}
			}
		}while($total > self::LIMIT * $n++);
		$dao->commit();
		
		if($exeFlag){
			//IDの対応表をデータベースに保持しておく
			SOYShop_DataSets::put("eccube_import.cat_cor_tbl", $this->oldIds);
			//親子関係の配列を変換後、データベースに保持しておく
			SOYShop_DataSets::put("eccube_import.cat_par_tbl", self::convertParentRelatives());
		}
		
		unset($dao);
		unset($exeFlag);
		unset($category);
		
		unset($r);
		unset($n);
		unset($total);
		unset($offset);
	}
	
	private function registerItem(){
		$this->parents = SOYShop_DataSets::get("eccube_import.cat_par_tbl", array());
		$this->oldIds = SOYShop_DataSets::get("eccube_import.cat_cor_tbl", array());
		$dao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		
		$n = 1;
		$total = self::getTotal("dtb_products_class");
		
		$dao->begin();
		do{
			$offset = self::LIMIT * ($n - 1) + 1;
			foreach($this->pdo->query(
				"SELECT product_id,product_code,stock,stock_unlimited,price01,price02 FROM dtb_products_class ORDER BY product_class_id ASC LIMIT " . self::LIMIT . " OFFSET " . $offset
			) as $r){
				try{
					$res = $dao->executeQuery("SELECT id FROM soyshop_item WHERE item_code = :code LIMIT 1;", array(":code" => self::t($r["product_code"])));
					if(count($res) > 0) continue;
				}catch(Exception $e){
					
				}
				$item = new SOYShop_Item();
				//商品コードや在庫数は先に入れておく
				$item->setCode(self::t($r["product_code"]));
				if((int)$r["stock_unlimited"] === 1){
					$item->setStock(2147483647);
				}else{
					$item->setStock((int)$r["stock"]);
				}
				//通常価格
				$item->setAttribute("list_price", $r["price01"]);
				
				//販売価格
				$item->setPrice($r["price02"]);
				$id = self::t($r["product_id"]);
				
				//商品の詳細情報を入れる
				foreach($this->pdo->query(
					"SELECT name,status,comment3,main_list_image,main_large_image,del_flg,create_date,update_date FROM dtb_products WHERE product_id = " . $id . " LIMIT 1"
				) as $r){
					$status = ((int)$r["status"] === 2) ? 0 : 1;
					$item->setName(self::t($r["name"]));
					$item->setIsOpen($status);
					$item->setAttribute("keywords", $r["comment3"]);
					
					//画像
					if(!file_exists(SOYSHOP_SITE_DIRECTORY . "files/" . $item->getCode() . "/")) mkdir(SOYSHOP_SITE_DIRECTORY . "files/" . $item->getCode() . "/");
					if(isset($r["main_list_image"])) $item->setAttribute("image_small", "/" . SOYSHOP_ID . "/files/save_image/" . $r["main_list_image"]);
					if(isset($r["main_large_image"])) $item->setAttribute("image_large", "/" . SOYSHOP_ID . "/files/save_image/" . $r["main_large_image"]);
					
					//登録時期
					$item->setCreateDate(self::convertTimestamp($r["create_date"]));
					$item->setUpdateDate(self::convertTimestamp($r["update_date"]));
					
					//削除の確認
					if((int)$r["del_flg"] > 0) {
						$item->setName($item->getName() . "(削除)");
						$item->setCode($item->getCode() . "_delete_0");
						$item->setAlias($item->getCode());
						$item->setIsDisabled(SOYShop_Item::IS_DISABLED);
					}
				}
				
				//詳細ページのページID
				$pageId = self::getDetailPageId();
				if($pageId > 0) $item->setDetailPageId($pageId);
				
				//カテゴリの取得
				foreach($this->pdo->query("SELECT category_id FROM dtb_product_categories WHERE product_id = " . $id . " LIMIT 1") as $r){
					$catId = self::getCategoryId((int)$r["category_id"]);
					if(isset($catId)) $item->setCategory($catId);
				}
				
				try{
					$dao->insert($item);
				}catch(Exception $e){
					//
				}
			}
		}while($total > self::LIMIT * $n++);
		$dao->commit();
		unset($dao);
		unset($item);
		
		unset($r);
		unset($n);
		unset($total);
		unset($offset);
	}
	
	private function registerOrder(){
		$dao = SOY2DAOFactory::create("order.SOYShop_OrderDAO");
		$attrDao = SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO");
		$logic = SOY2Logic::createInstance("logic.order.OrderLogic");
		
		$n = 1;
		$total = self::getTotal("dtb_order");
		
		$dao->begin();
		do{
			$offset = self::LIMIT * ($n - 1) + 1;
			foreach($this->pdo->query(
				"SELECT order_id,order_name01,order_name02,order_kana01,order_kana02,order_email,order_tel01,order_tel02,order_tel03,order_zip01,order_zip02,order_pref,order_addr01,order_addr02,payment_total,note,create_date FROM dtb_order ORDER BY order_id ASC LIMIT " . self::LIMIT . " OFFSET " . $offset
			) as $r){
				//顧客情報を取得する
				try{
					$res = $dao->executeQuery("SELECT id,name,reading,zip_code,area,address1,address2,telephone_number FROM soyshop_user WHERE mail_address = :email LIMIT 1", array(":email" => self::t($r["order_email"])));
					if(!isset($res[0]["id"])) continue;
				}catch(Exception $e){
					continue;
				}
				
				//同じ注文時間のものは厳密一つとする。顧客IDも見ておく
				$odate = self::convertTimestamp($r["create_date"]);
				try{
					$rst = $dao->executeQuery("SELECT id FROM soyshop_order WHERE user_id = :uid AND order_date = :date LIMIT 1", array(":uid" => $res[0]["id"], ":date" => $odate));
					if(count($rst) > 0) continue;
				}catch(Exception $e){
					//
				}
				
				$order = new SOYShop_Order();
				$order->setUserId((int)$res[0]["id"]);
				$order->setPrice((int)$r["payment_total"]);
				$order->setAddress(self::convertAddress($r));
				$order->setClaimedAddress(self::buildClaimedAddress($res[0]));
				$order->setAttribute("memo", $r["note"]);
				$order->setModules(null);
				$order->setOrderDate($odate);
				$order->setTrackingNumber($logic->getTrackingNumber($order));
				
				/**
				 * @ToDo 支払方法の情報はどうする？
				 */
				
				try{
					$id = $dao->insert($order);
				}catch(Exception $e){
					continue;
				}
				
				foreach($this->pdo->query(
					"SELECT product_code,price,quantity FROM dtb_order_detail WHERE order_id = " . (int)$r["order_id"]
				) as $rr){
					try{
						$res = $dao->executeQuery("SELECT id,item_name FROM soyshop_item WHERE item_code = :code", array(":code" => self::t($rr["product_code"])));
						if(count($res) === 0) continue;
					}catch(Exception $e){
						continue;
					}
					
					$iorder = new SOYShop_ItemOrder();
					$iorder->setOrderId($id);
					$iorder->setItemId((int)$res[0]["id"]);
					$iorder->setItemCount($rr["quantity"]);
					$iorder->setItemPrice($rr["price"]);
					$iorder->setTotalPrice($rr["quantity"] * $rr["price"]);
					$iorder->setItemName($res[0]["item_name"]);
					$iorder->setCdate($odate);
					
					try{
						$attrDao->insert($iorder);
					}catch(Exception $e){
						//
					}
				}
			}
		}while($total > self::LIMIT * $n++);
		$dao->commit();
		
		unset($odate);
		unset($r);
		unset($rr);
		unset($order);
		
		unset($dao);
		unset($attrDao);
		unset($logic);
	}
	
	/** getter **/
	private function getTotal($db){
		if(strlen($db) === 0) return 0;
		foreach($this->pdo->query("SELECT COUNT(*) FROM " . $db) as $r){
			return (isset($r[0])) ? (int)$r[0] : 0;
		}
		return 0;
	}
	
	private function getCategoryId($id){
		//子カテゴリからカテゴリIDを探していく
		for($i = count($this->parents) - 1; $i >= 0; $i--){
			foreach($this->parents[$i] as $catId){
				if($catId === $id && isset($this->oldIds[$catId])){
					return (int)$this->oldIds[$catId];
				}
			}
		}
		//ヒットしなければnull
		return null;
	}
	
	private function getDetailPageId(){
		static $id;
		if(is_null($id)){
			
			$dao = SOY2DAOFactory::create("site.SOYShop_PageDAO");
			try{
				$res = $dao->executeQuery("SELECT id FROM soyshop_page WHERE type = :type ORDER BY id ASC LIMIT 1;", array(":type" => SOYShop_Page::TYPE_DETAIL));
			}catch(Exception $e){
				$res = array();
			}
			
			$id = (isset($res[0]["id"])) ? (int)$res[0]["id"] : 0;
			unset($dao);
		}
		return $id;
	}
	
	/** ビルダー **/
	private function buildClaimedAddress($u){
		return array(
			"name" => $u["name"],
			"reading" => $u["reading"],
			"zipCode" => $u["zip_code"],
			"area" => $u["area"],
			"address1" => $u["address1"],
			"address2" => $u["address2"],
			"telephoneNumber" => $u["telephone_number"],
			"office" => ""
		);
	}
	
	/** コンバート系 **/
	
	private function convertTimestamp($time){
		$array = explode(" ", $time); //[0]には日、[1]には時間
		$dates = explode("-", $array[0]);
		$times = explode(":", $array[1]);
		return mktime($times[0], $times[1], $times[2], $dates[1], $dates[2], $dates[0]);		//時、分、秒、月、日、年
	}
	
	private function convertParentRelatives(){
		$parents = array();
		foreach($this->relatives as $id => $rel){
			if(is_null($rel)) continue;
			$this->parents[$rel][] = $id;
		}
		
		return $this->parents;
	}
	
	private function convertAddress($r){
		return array(
			"name" => $r["order_name01"] . " " . $r["order_name02"],
			"reading" => $r["order_kana01"] . " "  . $r["order_kana02"],
			"zipCode" => $r["order_zip01"] . $r["order_zip02"],
			"area" => $r["order_pref"],
			"address1" => $r["order_addr01"],
			"address2" => $r["order_addr02"],
			"telephoneNumber" => $r["order_tel01"] . $r["order_tel02"] . $r["order_tel03"],
			"office" => ""
		);
	}
	
	private function pdo(){
		SOY2::import("module.plugins.eccube_data_import.util.EccubeDataImportUtil");
		$config = EccubeDataImportUtil::getConfig();

		$dsn = "mysql:host=" . self::ent($config["host"]) . ";dbname=" . self::ent($config["db"]);
		if(isset($config["port"]) && strlen($config["port"])) $dsn .= ";port=" . self::ent($config["port"]);
		$dsn .= ";charset=utf8";// PHP 5.3.6未満では無視される
		
		try{
			$this->pdo = new PDO($dsn, self::ent($config["user"]), self::ent($_POST["Password"]));
		}catch(Exception $e){
			//
		}
	}
	
	/**
	 * 文字列をHTMLエンティティに変換する
	 * @params String s
	 * @return String s
	 */
	private function ent($s){
		return htmlspecialchars($s, ENT_QUOTES, "UTF-8");
	}
	
	/**
	 * トリム
	 */
	private function t($s){
		return trim($s);
	}
}
?>