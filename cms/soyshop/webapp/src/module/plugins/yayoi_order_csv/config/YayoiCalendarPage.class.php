<?php

class YayoiCalendarPage extends WebPage{

	private $configObj;

	private $y;
	private $m;
	private $yayoiDao;
	private $itemOrderDao;

	const TYPE_TOKUI = "tokdai";
	const TYPE_URIAGE = "uriage";

	function __construct(){
		$this->y = (isset($_GET["y"])) ? (int)$_GET["y"] : (int)date("Y");
		$this->m = (isset($_GET["m"])) ? (int)$_GET["m"] : (int)date("n");
		SOY2::imports("module.plugins.yayoi_order_csv.domain.*");
		SOY2::imports("module.plugins.yayoi_order_csv.component.*");
		SOY2::import("domain.order.SOYShop_Order");
		$this->yayoiDao = SOY2DAOFactory::create("SOYShop_YayoiOutputDAO");
		$this->itemOrderDao = SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO");

		SOY2::import("domain.config.SOYShop_Area");
		SOY2::import("domain.order.SOYShop_ItemModule");
	}

	function doPost(){

		$errMsg = null;

		if(isset($_POST["day"]) && count($_POST["day"])){
			$csvLogic = SOY2Logic::createInstance("module.plugins.yayoi_order_csv.logic.ExportCsvLogic");

			$csvType = (isset($_POST["csv"])) ? $_POST["csv"] : self::TYPE_TOKUI;

			$this->yayoiDao->begin();

			//注文時刻で集める
//			$sql = "SELECT * FROM soyshop_order ".
//					"WHERE order_status > " . SOYShop_Order::ORDER_STATUS_INTERIM . " ".
//					"AND order_status < " . SOYShop_Order::ORDER_STATUS_CANCELED . " ".
//					"AND order_date > :start ".
//					"AND order_date < :end";

			//発送時刻で調べる
			$sql = "SELECT DISTINCT o.id, o.*, u.attribute1 FROM soyshop_order o ".
					"INNER JOIN soyshop_order_state_history h ".
					"ON o.id = h.order_id " .
					"INNER JOIN soyshop_user u ".
					"ON o.user_id = u.id ".
					"WHERE o.order_status > " . SOYShop_Order::ORDER_STATUS_RECEIVED . " ".
					"AND o.order_status < " . SOYShop_Order::ORDER_STATUS_CANCELED . " ".
					"AND h.content LIKE '%「発送済み」%' ".
					"AND h.order_date > :start ".
					"AND h.order_date < :end";

			$slipNumLogic = SOY2Logic::createInstance("module.plugins.slip_number.logic.SlipNumberLogic");

			$lines = array();

			foreach($_POST["day"] as $d){
				$start = mktime(0, 0, 0, $this->m, $d, $this->y);
				$end = $start + 24*60*60 - 1;

				try{
					$res = $this->yayoiDao->executeQuery($sql, array(":start" => $start, ":end" => $end));
				}catch(Exception $e){
					continue;
				}

				foreach($res as $v){

					//前に発送済みにしていないか調べる
					if(!self::checkBeforeChange($v["id"], $start)) continue;

					//どちらでも使用する項目
					$claimed = soy2_unserialize($v["claimed_address"]);
					$tel = self::removeHyphen($claimed["telephoneNumber"]);

					//卸
					$isWhole = (isset($v["attribute1"]) && trim($v["attribute1"]) === "卸");

					//ここで分岐
					if($csvType == self::TYPE_TOKUI){
						$line = array();

						$adr1 = SOYShop_Area::getAreaText($claimed["area"]) . $claimed["address1"];
						$line[] = $tel;		//得意先コード 電話番号ハイフンを取り除いたもの
						$line[] = $claimed["name"];		//得意先名称
						$line[] = mb_convert_kana($claimed["reading"], "k");		//得意先フリガナ 半角カナ
						$line[] = mb_convert_kana($claimed["reading"], "k");		//略称　半角カナ
						$line[] = self::removeHyphen($claimed["zipCode"]);		//郵便番号
						$line[] = $adr1;		//住所1
						$line[] = $claimed["address2"].$claimed["address3"];		//住所2
						$line[] = "";		//部署名
						$line[] = "";		//役職名
						$line[] = "";		//ご担当者
						$line[] = "様";		//敬称
						$line[] = $tel;		//TEL
						$line[] = "";		//FAX
						$line[] = ($isWhole) ? "0010" : "0000";		//分類1 卸の文字列で0000から0010へ
						$line[] = ($isWhole) ? "0011" : "0000";		//分類2 卸の文字列で0000から0011へ
						$line[] = "";		//分類3
						$line[] = "";		//分類4
						$line[] = "";		//分類5
						$line[] = "";		//企業コード
						$line[] = "10000001";	//指定売上伝票
						$line[] = "4202";		//指定請求書
						$line[] = "0";		//DM発行
						$line[] = "4";		//取引区分
						$line[] = "1";		//単価区分
						$line[] = "100";	//掛率
						$line[] = $tel;		//請求先コード 電話番号ハイフンを取り除いたもの
						$line[] = "4";		//締グループ
						$line[] = "1";		//税転嫁
						$line[] = "201";	//回収方法
						$line[] = "1";		//回収サイクル
						$line[] = "";		//回収日
						$line[] = "2";		//手数料負担区分
						$line[] = "";		//手形サイト
						$line[] = "0";		//与信限度額
						$line[] = "3";		//金額端数処理
						$line[] = "3";		//税端数処理
						$line[] = ($isWhole) ? "003" : "001";		//担当者コード 卸の文字列で001から003へ
						$line[] = $adr1;		//メモ欄　都道府県 + 市区町村
						$line[] = self::getMailAdressByUserId($v["user_id"]);		//メールアドレス
						$line[] = "";		//ホームページ
						$line[] = "1";		//参照表示
						$line[] = "";		//請求書合算コード
						$line[] = "";		//未使用

						$lines[] = implode(",", $line);

					}else if($csvType == self::TYPE_URIAGE){

						$itemOrders = self::getItemOrdersByOrderId($v["id"]);
						if(!count($itemOrders)) continue;

						//手数料を追加
						$mods = soy2_unserialize($v["modules"]);
						foreach($mods as $mod){
							if((int)$mod->getPrice() === 0) continue;

							//税金を含めない
							if(strpos($mod->getId(), "_tax") !== false) continue;

							$obj = new SOYShop_ItemOrder();
							$obj->setItemName($mod->getName());
							$obj->setItemCount(1);
							$obj->setItemPrice($mod->getPrice());
							$obj->setTotalPrice($obj->getItemPrice());

							$itemOrders[] = $obj;
						}

						$outputDate = date("Y/m/d");
						//$slipNumber = $slipNumLogic->getAttribute($v["id"])->getValue1();

						$n = 1;
						foreach($itemOrders as $itemOrder){

							$itemCode = self::getItemCodeByItemId($itemOrder->getItemId());
							if(is_null($itemCode) || !strlen($itemCode)){
								/** @ToDo 特別な項目は管理画面で設定できるようにしたい **/
								if(strpos($itemOrder->getItemName(), "代金") !== false){
									$itemCode = 810;
								}else if(strpos($obj->getItemName(), "送料") !== false){
									$itemCode = 800;
								}
							}

							$line = array();
							$line[] = "1";		//固定
							$line[] = "1";		//固定
							$line[] = "0";		//固定
							$line[] = $outputDate;		//データ出力日
							//$line[] = $slipNumber;		//伝票番号
							$line[] = $v["tracking_number"];//注文番号らしい
							$line[] = "24";		//固定
							$line[] = "4";		//固定
							$line[] = "1";		//固定
							$line[] = "3";		//固定
							$line[] = "3";		//固定
							$line[] = $tel;		//得意先コード（電話番号ハイフン除く）
							$line[] = "";
							$line[] = "2";		//固定
							$line[] = $n++;		//項目番号 連番
							$line[] = "1";		//固定
							$line[] = $itemCode;		//商品コード
							$line[] = "";
							$line[] = $itemOrder->getItemName();		//商品名
							$line[] = "12";		//固定
							$line[] = "";
							$line[] = "1";		//固定
							$line[] = "";
							$line[] = "";
							$line[] = $itemOrder->getItemCount();		//数量
							$line[] = $itemOrder->getItemPrice();		//金額
							$line[] = $itemOrder->getTotalPrice();		//合計金額
							$line[] = "";
							$line[] = "";
							$line[] = "";
							$line[] = "";
							$line[] = "";
							$line[] = "2";
							$line[] = "2";
							$line[] = "";
							$line[] = "";
							$line[] = "";
							$line[] = "";
							$line[] = "";
							$line[] = "";
							$line[] = $claimed["name"];		//名前

							$lines[] = implode(",", $line);
						}
					}else{
						//
					}
				}

				$obj = new SOYShop_YayoiOutput();
				$obj->setOutputDate($start);

				try{
					$this->yayoiDao->insert($obj);
				}catch(Exception $e){
					//
				}
			}

			$this->yayoiDao->commit();

			//ここからCSVの出力
			if(count($lines)){

				$charset = (isset($_REQUEST["charset"])) ? $_REQUEST["charset"] : "Shift_JIS";

				header("Cache-Control: public");
				header("Pragma: public");
				header("Content-Disposition: attachment; filename=yayoi_" . $csvType . "_" . date("YmdHis") . ".csv");
				header("Content-Type: text/csv; charset=" . htmlspecialchars($charset) . ";");

				ob_start();
				//echo implode("," , $csvLogic->getLabels());
				//echo "\r\n";
				echo implode("\r\n", $lines);
				$csv = ob_get_contents();
				ob_end_clean();

				echo mb_convert_encoding($csv, $charset, "UTF-8");

				exit;	//csv output

			}else{
				$errMsg = "注文データがありませんでした。<br>";
			}
		}else{
			$errMsg = "日付を一つ以上選択してから出力ボタンを押してください。<br>";
		}

		if(!is_null($errMsg)){
			echo $errMsg;
			echo "このタブを閉じ、前の画面でF5を押して、ページを再読み込みしてください。";
			exit;
		}
	}

	private function checkBeforeChange($orderId, $start){
		$sql = "SELECT * FROM soyshop_order_state_history ".
				"WHERE order_id = :orderId ".
				"AND content LIKE '%「発送済み」%' ".
				"AND order_date < :orderDate ";

		try{
			$res = $this->yayoiDao->executeQuery($sql, array(":orderId" => $orderId, ":orderDate" => $start));
		}catch(Exception $e){
			return true;
		}

		//一つでもあればfalse
		return (count($res) === 0);
	}

	function execute(){
		parent::__construct();

		DisplayPlugin::toggle("successed", isset($_GET["successed"]));
		DisplayPlugin::toggle("failed", isset($_GET["failed"]));

		$this->addForm("form");

		//最初の注文があった日の年
		$this->addSelect("year", array(
			"name" => "year",
			"options" => self::getYearRange(),
			"selected" => $this->y,
			"id" => "year",
			"onChange" => "redirectAfterSelect();"
		));

		$this->addSelect("month", array(
			"name" => "month",
			"options" => range(1,12),
			"selected" => $this->m,
			"id" => "month",
			"onChange" => "redirectAfterSelect();"
		));

		$this->addLabel("calendar", array(
			"html" => SOY2Logic::createInstance("module.plugins.yayoi_order_csv.logic.BuildCalendarLogic", array("outputDateList" => $this->yayoiDao->getExecutedOutputDate($this->y, $this->m)))->build($this->y, $this->m, false)
		));

		$this->createAdd("output_histories_list", "OutPutHistoryListComponent", array(
			"list" => self::get()
		));
	}

	private function getYearRange(){
		$firstOrderYear = self::getFirstOrderYear();
		if($firstOrderYear == date("Y")) return array(date("Y"));

		$list = array();
		for ($i = 0; $i <= date("Y") - $firstOrderYear; $i++){
			$list[] = $firstOrderYear + $i;
		}

		return $list;
	}

	private function getFirstOrderYear(){
		$dao = new SOY2DAO();
		try{
			$res = $dao->executeQuery("SELECT order_date FROM soyshop_order ORDER BY order_date ASC LIMIT 1");
		}catch(Exception $e){
			return date("Y");
		}

		if(!isset($res[0]["order_date"])) return date("Y");

		return date("Y", $res[0]["order_date"]);
	}

	private function getMailAdressByUserId($userId){
		try{
			$res = $this->yayoiDao->executeQuery("SELECT mail_address FROM soyshop_user WHERE id = :id", array(":id" => $userId));
		}catch(Exception $e){
			return "";
		}

		return (isset($res[0]["mail_address"])) ? $res[0]["mail_address"] : "";
	}

	private function getItemOrdersByOrderId($orderId){
		try{
			return $this->itemOrderDao->getByOrderId($orderId);
		}catch(Exception $e){
			return array();
		}
	}

	private function getItemCodeByItemId($itemId){
		try{
			$res = $this->yayoiDao->executeQuery("SELECT item_code FROM soyshop_item WHERE id = :id", array(":id" => $itemId));
		}catch(Exception $e){
			return null;
		}

		return (isset($res[0]["item_code"])) ? $res[0]["item_code"] : null;
	}

	private function removeHyphen($str){
		$str = str_replace(array("-", "ー"), "", $str);
		return mb_convert_kana($str, "a");
	}

	private function get(){
		$this->yayoiDao->setLimit(30);
		try{
			return $this->yayoiDao->get();
		}catch(Exception $e){
			return array();
		}
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
?>
