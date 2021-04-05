<?php
SOY2::import("domain.config.SOYShop_ShopConfig");
class DetailPage extends WebPage{

	function doPost(){
		if(!AUTH_OPERATE || !AUTH_CHANGE) return;	//操作権限がないアカウントの場合は以後のすべての動作を封じる

		if(!empty($_FILES) && empty($_POST)){
			$dao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
			$attrDAO = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
			$item = $dao->getById($this->id);

			$res = array();
			foreach($_FILES["custom_field"]["name"] as $key => $filename){
				$filename = strtolower(str_replace("%","",rawurlencode($filename)));
				$tmpname = $_FILES["custom_field"]["tmp_name"][$key];
				$pathinfo = pathinfo($filename);

				//get unique file name
				$counter = 0;
				$filepath = "";

				SOYShopPlugin::load("soyshop.upload.image");
				$name = SOYShopPlugin::invoke("soyshop.upload.image", array(
					"mode" => "item",
					"item" => $item,
					"pathinfo" => $pathinfo
				))->getName();

				if(is_null($name) || !strlen($name)){
					$counter = 0;
					while(true){
						$name = ($counter > 0) ? $pathinfo["filename"] . "_" . $counter . "." . $pathinfo["extension"] : $pathinfo["filename"] . "." . $pathinfo["extension"];
						if(!file_exists($item->getAttachmentsPath() . $name)) break;
						$counter++;
					}
				}

				//get unique file name
				$filepath = $item->getAttachmentsPath() . $name;

				//一回でも失敗した場合はfalseを返して終了（rollbackは無し）
				$result = move_uploaded_file($tmpname,$filepath);
				@chmod($filepath,0604);

				if(!$result){
					$res = array(
						"result" => -1,
						"message" => "失敗しました"
					);
					break;
				}

				$res = array(
					"result" => filesize($filepath),
					"url" => $item->getAttachmentsUrl() . $name,
					"message" => "アップロードしました\nURL=" . $item->getAttachmentsUrl() . $name.""
				);

				try{
					$field = $attrDAO->get($this->id,$key);
					$field->setValue($item->getAttachmentsUrl() . $name);
					$attrDAO->update($field);
				}catch(Exception $e){
					$field = new SOYShop_ItemAttribute();
					$field->setItemId($item->getId());
					$field->setFieldId($key);
					$field->setValue($item->getAttachmentsUrl() . $name);
					$attrDAO->insert($field);
				}
			}

			echo json_encode($res);

			exit;
		}

		if(isset($_POST["Item"]) && soy2_check_token()){

			//マルチカテゴリモードの時、カテゴリ配列から一番最初の値を取得しておく
			if(isset($_POST["Item"]["multi"])){
				$categories = explode(",", $_POST["Item"]["multi"]["categories"]);
				//配列を綺麗にする
				$array = array();
				foreach($categories as $category){
					if(strlen($category) > 0)$array[] = $category;
				}
				$categories = $array;
				sort($categories);
				$_POST["Item"]["category"] = $categories[0];
			}

			$dao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
			$logic = SOY2Logic::createInstance("logic.shop.item.ItemLogic");

			$newItem = $_POST["Item"];

			//販売期間(注文受付期間)をタイムスタンプに変換
			if(isset($newItem["orderPeriodStart"]) && strlen($newItem["orderPeriodStart"]) > 0){
				$newItem["orderPeriodStart"] = soyshop_convert_timestamp($newItem["orderPeriodStart"], "start");
			}

			if(isset($newItem["orderPeriodEnd"]) && strlen($newItem["orderPeriodEnd"]) > 0){
				$newItem["orderPeriodEnd"] = soyshop_convert_timestamp($newItem["orderPeriodEnd"], "end");
			}

			//公開期限をタイムスタンプに変換
			if(isset($newItem["openPeriodStart"]) && strlen($newItem["openPeriodStart"]) > 0){
				$newItem["openPeriodStart"] = soyshop_convert_timestamp($newItem["openPeriodStart"], "start");
			}

			if(isset($newItem["openPeriodEnd"]) && strlen($newItem["openPeriodEnd"]) > 0){
				$newItem["openPeriodEnd"] = soyshop_convert_timestamp($newItem["openPeriodEnd"], "end");
			}

			$item = $dao->getById($this->id);

			//在庫のチェック
			$oldStock = $item->getStock();

			$obj = (object)$newItem;

			SOY2::cast($item, $obj);

			$alias = null;
			if(isset($_POST["custom_alias"])){
				$alias = $_POST["custom_alias"];
				$item->setAlias($alias);
			}

			if($logic->validate($item)){

				if(isset($_POST["do_close"])){
					$item->setIsOpen(SOYShop_Item::NO_OPEN);
				}
				if(isset($_POST["do_open"])){
					$item->setIsOpen(SOYShop_Item::IS_OPEN);
				}

                //作成日の値が空の場合は今の時刻を入れる
                if(is_null($item->getCreateDate())){
                    $item->setCreateDate(time());
                }

				$logic->update($item,$alias);
				$id = $this->id;

				SOYShopPlugin::load("soyshop.item.customfield");
				SOYShopPlugin::invoke("soyshop.item.customfield", array(
					"item" => $item
				));

				//マルチカテゴリモード
				if(isset($categories) && is_array($categories)){
					$logic->updateCategories($categories, $id);
				}

				SOYShopPlugin::load("soyshop.item.update");
				SOYShopPlugin::invoke("soyshop.item.update", array(
					"item" => $item,
					"old" => $oldStock
				));

				//商品名だけに特化した拡張ポイント
				SOYShopPlugin::load("soyshop.item.name");
				SOYShopPlugin::invoke("soyshop.item.name", array(
					"item" => $item
				));

				//会員特別価格の拡張ポイント
				SOYShopPlugin::load("soyshop.add.price");
				SOYShopPlugin::invoke("soyshop.add.price", array(
					"item" => $item
				));

				//商品の価格に特化した拡張ポイント
				SOYShopPlugin::load("soyshop.price.option");
				SOYShopPlugin::invoke("soyshop.price.option", array(
					"item" => $item
				));

				//管理画面からの注文で商品情報を変更したい時
				if(isset($_GET["registration_in"])){
					SOY2PageController::jump("Order.Register.Item");
				}else{
					SOY2PageController::jump("Item.Detail.$id?updated");
				}


				exit;
			}


			$this->obj = $item;
			$this->errors = $logic->getErrors();
		}

		if(isset($_POST["upload"])){
			$urls = $this->uploadImage();

			echo "<html><head>";
			echo "<script type=\"text/javascript\">";
			if($urls !== false){
				foreach($urls as $url){
					echo 'window.parent.ImageSelect.notifyUpload("' . $url . '");';
				}
			}else{
				echo 'alert("failed");';
			}
			echo "</script></head><body></body></html>";
			exit;
		}
	}

	var $id;
	var $errors = array();
	var $obj;
	var $config;

	function __construct($args) {
		$this->id = (isset($args[0])) ? (int)$args[0] : null;

		$this->config = SOYShop_ShopConfig::load();
		MessageManager::addMessagePath("admin");

		parent::__construct();

		$this->addLabel("user_label", array("text" => SHOP_USER_LABEL));

		//詳細ページを開いた時に何らかの処理をする
		SOYShopPlugin::load("soyshop.item");
		SOYShopPlugin::invoke("soyshop.item", array(
			"mode" => "detail",
			"itemId" => $this->id
		));

		DisplayPlugin::toggle("copy", (isset($_GET["copy"])));
		DisplayPlugin::toggle("error", (isset($_GET["error"])));

		//管理画面からの注文の際に表示する
		DisplayPlugin::toggle("registration_in_1", (isset($_GET["registration_in"])));
		DisplayPlugin::toggle("registration_in_2", (isset($_GET["registration_in"])));

		$this->addForm("update_form");

		self::_buildForm($this->id);
		//入荷通知周り
		self::buildNoticeButton();
		self::buildFavoriteButton();
	}

	private function _buildForm($id){

		$item = ($this->obj) ? $this->obj : soyshop_get_item_object($id);
		if(is_null($item->getId())){
			SOY2PageController::jump("Item");
			exit;
		}

		//削除フラグのチェック
		if($item->getIsDisabled() == SOYShop_Item::IS_DISABLED){
			SOY2PageController::jump("Item");
		}

		$readOnly = (!AUTH_OPERATE);

		$this->addLabel("open_text", array(
			"text" => "[" . $item->getPublishText() . "]",
			"visible" => ($item->getIsOpen() < SOYShop_Item::IS_OPEN)
		));

		DisplayPlugin::toggle("sale", $item->isOnSale());
		DisplayPlugin::toggle("item_name_wrap", ($item->getIsOpen() < SOYShop_Item::IS_OPEN) || ($item->isOnSale()));

		$this->addLabel("item_name_text", array(
			"text" => $item->getName()
		));

		$this->addInput("item_name", array(
			"name" => "Item[name]",
			"value" => $item->getName(),
			"readonly" => $readOnly
		));

		SOYShopPlugin::load("soyshop.item.name");
		$this->addLabel("extension_item_name_input", array(
			"html" => SOYShopPlugin::display("soyshop.item.name", array("item" => $item))
		));

		$this->addInput("item_code", array(
			"name" => "Item[code]",
			"value" => $item->getCode(),
			"readonly" => $readOnly
		));

		$isIgnoreStock = ($this->config->getIgnoreStock() && $this->config->getIsHiddenStockCount());
		DisplayPlugin::toggle("item_stock", !$isIgnoreStock);
		$this->addInput("item_stock", array(
			"name" => "Item[stock]",
			"value" => $item->getStock(),
			"readonly" => (SOYShopPluginUtil::checkIsActive("reserve_calendar"))
		));

		$this->addInput("item_unit", array(
			"name" => "Item[unit]",
			"value" => (!is_null($item->getUnit())) ? $item->getUnit() : SOYShop_Item::UNIT,
			"style" => "width:80px"
		));

		//注文数
		$this->addLabel("item_order_count", array(
			"text" => self::_getOrderCount($item)
		));

		//仕入値
		$this->addInput("item_purchase_price", array(
			"name" => "Item[price]",
			"value" => $item->getPurchasePrice(),
			"readonly" => $readOnly
		));

		//通常価格
		$this->addInput("item_normal_price", array(
			"name" => "Item[price]",
			"value" => $item->getPrice(),
			"readonly" => $readOnly
		));

		//セール価格
		$this->addInput("item_sale_price", array(
			"name" => "Item[salePrice]",
			"value" => $item->getSalePrice(),
			"readonly" => $readOnly
		));

		$this->addCheckBox("item_is_sale", array(
			"elementId" => "item_is_sale",
			"name" => "Item[saleFlag]",
			"value" => SOYShop_Item::IS_SALE,
			"isBoolean" => true,
			"selected" => ($item->isOnSale()),
		));

		//定価
		$this->addInput("item_list_price", array(
			"name" => "Item[config][list_price]",
			"value" => soyshop_check_price_string($item->getAttribute("list_price")),
			"readonly" => $readOnly
		));

		SOYShopPlugin::load("soyshop.add.price");
		$this->addLabel("extension_add_price_area", array(
			"html" => SOYShopPlugin::display("soyshop.add.price", array("item" => $item))
		));

		SOYShopPlugin::load("soyshop.price.option");
		$this->createAdd("extension_price_option_list", "_common.Item.PriceOptionListComponent", array(
			"list" => SOYShopPlugin::invoke("soyshop.price.option", array("item" => $item))->getContents()
		));

		try{
			$detailPages = SOY2DAOFactory::create("site.SOYShop_PageDAO")->getByType(SOYShop_Page::TYPE_DETAIL);
		}catch(Exception $e){
			$detailPages = array();
		}

		DisplayPlugin::toggle("detail_page_id_select", count($detailPages));

		$editable = false;
		$url = "";

		if(count($detailPages)){
			$page = soyshop_get_page_object($item->getDetailPageId());
			if(is_numeric($page->getId())){
				$url = soyshop_get_page_url($page->getUri(), $item->getAlias());
				$url = str_replace($item->getAlias(), "<b>" . $item->getAlias() . "</b>", $url);
				$editable = true;
			}else{
				$url = MessageManager::get("ERROR_ITEM_SELECT_DETAIL_PAGE");
			}
		}

		$this->addLabel("item_url_text", array(
			"html" => $url
		));

		DisplayPlugin::toggle("item_alias_edit", $editable);
		$this->addInput("item_alias", array(
			"name" => "custom_alias",
			"value" => (isset($_POST["custom_alias"])) ? $_POST["custom_alias"] : $item->getAlias(),
			"readonly" => $readOnly
		));

		$this->addModel("custom_alias_input", array(
			"style" => (isset($this->errors["alias"])) ? "" : "display:none;"
		));

		$this->addSelect("detail_page_list", array(
			"name" => "Item[detailPageId]",
			"options" => $detailPages,
			"selected" => $item->getDetailPageId(),
			"property" => "name"
		));

		/* category */
		$categories = soyshop_get_category_objects();
		$categoryCount = count($categories);
		DisplayPlugin::toggle("has_category", $categoryCount);
		DisplayPlugin::toggle("no_category", !$categoryCount);
		$this->createAdd("category_tree","_base.MyTreeComponent", array(
			"list" => $categories,
			"selected" => array($item->getCategory())
		));

		$this->addInput("item_category", array(
			"name" => "Item[category]",
			"value" =>$item->getCategory(),
			"attr:id" => "item_category"
		));

		$category = (isset($categories[$item->getCategory()])) ? $categories[$item->getCategory()] : new SOYShop_Category();
		$this->addLabel("item_category_choice", array(
			"text" => self::_getCategoryRelation($category),
			"attr:id" => "item_category_text"
		));

		DisplayPlugin::toggle("item_category_area", (!$item->isChild() && $this->config->getMultiCategory() != 1));
		DisplayPlugin::toggle("multi_category_area", (!$item->isChild() && $this->config->getMultiCategory() != 0));

		$this->addInput("multi_category", array(
			"name" => "Item[multi][categories]",
			"value" => implode(",",$this->getCategoryIds()),
			"attr:id" => "multi_category"
		));

		$this->addLabel("multi_category_text", array(
			"text" => $this->getCategoriesName($categories),
			"attr:id" => "multi_category_text"
		));

		$this->createAdd("multi_category_tree", "_base.MyTreeComponent", array(
			"list" => $categories,
			"selected" => $this->getCategoryIds(),
			"func" => "onMultiClickLeaf"
		));

		/* parent item */
		DisplayPlugin::toggle("item_parent_area", $item->isChild());
		$parentItem = soyshop_get_item_object($item->getType());

		$this->addLink("parent_item_link", array(
			"link" => SOY2PageController::createLink("Item.Detail." . $item->getType()),
			"text" => (is_numeric($parentItem->getId())) ? $parentItem->getName() : "[この商品グループは削除されています]"
		));

		/* child item */
		DisplayPlugin::toggle("child_item_list_area", ($item->getType() == SOYShop_Item::TYPE_GROUP || $item->getType() == SOYShop_Item::TYPE_DOWNLOAD_GROUP));

		$getParam = ($item->getType() == SOYShop_Item::TYPE_GROUP) ? "parent" : "dlparent";
		$this->addLink("add_child_item", array(
			"link" => SOY2PageController::createLink("Item.Create") . "?" . $getParam . "=" . $item->getId()
		));

		$children = soyshop_get_item_children($item->getId());

		DisplayPlugin::toggle("children", count($children));
		$this->createAdd("child_item_list","HTMLList", array(
			"list" => $children,
			'populateItem:function($entity,$key)' => '$itemName = $entity->getName();'.
				'if($entity->getIsOpen() != 1) $itemName = "(非公開)" . $itemName;'.
				'if($entity->getIsDisabled() != 0) $itemName .= "(削除)" . $itemName;'.
				'if($entity->getIsOpen() != 1 || $entity->getIsDisabled() != 0) $itemName = "<span style=\"color:#787878;font-size:0.9em;\">" . $itemName . "</span>";'.
				'$this->createAdd("item_detail_link","HTMLLink", array(' .
					'"link" => "'.SOY2PageController::createLink("Item.Detail").'/" . $entity->getId(),' .
					'"html" => $itemName
				));'.
				'$this->addLabel("item_price", array(' .
					'"text" => soy2_number_format($entity->getPrice())
				));'
		));

		/* config */
		DisplayPlugin::toggle("item_description", $this->config->getDisplayItemDescription());
		$this->addTextArea("item_description", array(
			"name" => "Item[config][description]",
			"value" => $item->getAttribute("description"),
			"readonly" => $readOnly
		));

		DisplayPlugin::toggle("item_keywords", $this->config->getDisplayItemKeywords());
		$this->addInput("item_keywords", array(
			"name" => "Item[config][keywords]",
			"value" => $item->getAttribute("keywords"),
			"readonly" => $readOnly
		));

		DisplayPlugin::toggle("item_image", $this->config->getDisplayItemImage());
		$this->createAdd("item_small_image","_common.Item.ImageSelectComponent", array(
			"domId" => "item_small_image",
			"name" => "Item[config][image_small]",
			"value" => soyshop_convert_file_path_on_admin($item->getAttribute("image_small"))
		));

		$this->createAdd("item_large_image","_common.Item.ImageSelectComponent", array(
			"domId" => "item_large_image",
			"name" => "Item[config][image_large]",
			"value" => soyshop_convert_file_path_on_admin($item->getAttribute("image_large"))
		));

		//error
		foreach(array("name","code","alias") as $key){
			$this->addLabel("error_$key", array(
				"text" => (isset($this->errors[$key])) ? $this->errors[$key] : "",
				"visible" => (isset($this->errors[$key]) && strlen($this->errors[$key]))
			));
		}

		SOYShopPlugin::load("soyshop.item.customfield");
		$this->addLabel("custom_field", array(
			"html" => SOYShopPlugin::display("soyshop.item.customfield", array("item" => $item))
		));

		//upload
		$this->addForm("upload_form");

		$this->createAdd("image_list","_common.Item.ItemImageListComponent", array(
			"list" => self::_getAttachments($item)
		));

		//管理制限の権限を取得し、権限がない場合は表示しない
		foreach(range(1,4) as $i){
			DisplayPlugin::toggle("app_limit_function_" . $i, AUTH_OPERATE);
		}
		foreach(range(1,2) as $i){
			DisplayPlugin::toggle("app_limit_function_change_" . $i, AUTH_CHANGE);
		}


		//注文受付期間(終売品向け機能)
		$this->addInput("item_order_period_start", array(
			"name" => "Item[orderPeriodStart]",
			"value" => soyshop_convert_date_string($item->getOrderPeriodStart()),
			"id" => "order_period_start",
			"readonly" => true,
		));

		$this->addInput("item_order_period_end", array(
			"name" => "Item[orderPeriodEnd]",
			"value" => soyshop_convert_date_string($item->getOrderPeriodEnd()),
			"id" => "order_period_end",
			"readonly" => true
		));

		//公開期間
		$this->addInput("item_open_period_start", array(
			"name" => "Item[openPeriodStart]",
			"value" => soyshop_convert_date_string($item->getOpenPeriodStart()),
			"id" => "open_period_start",
			"readonly" => true
		));

		$this->addInput("item_open_period_end", array(
			"name" => "Item[openPeriodEnd]",
			"value" => soyshop_convert_date_string($item->getOpenPeriodEnd()),
			"id" => "open_period_end",
			"readonly" => true
		));
	}

	//入荷通知周り
	private function buildNoticeButton(){

		$isNoticeArrival = (class_exists("SOYShopPluginUtil") && (SOYShopPluginUtil::checkIsActive("common_notice_arrival")));

		//プラグインがアクティブでないと、顧客数を取得しにいかない
		if($isNoticeArrival){
			$noticeLogic = SOY2Logic::createInstance("module.plugins.common_notice_arrival.logic.NoticeLogic");
			$users = $noticeLogic->getUsersByItemId($this->id, SOYShop_NoticeArrival::NOT_SENDED, SOYShop_NoticeArrival::NOT_CHECKED);
			$isNoticeArrival = (count($users));
		}

		//プラグインがアクティブになっていること、顧客数が一人以上いる場合に表示する
		DisplayPlugin::toggle("notice_arrival", $isNoticeArrival);
	}

	//入荷通知周り
	private function buildFavoriteButton(){

		$isFavorite = (class_exists("SOYShopPluginUtil") && (SOYShopPluginUtil::checkIsActive("common_favorite_item")));

		//プラグインがアクティブでないと、顧客数を取得しにいかない
		if($isFavorite){
			$favoriteLogic = SOY2Logic::createInstance("module.plugins.common_favorite_item.logic.FavoriteLogic");
			$users = $favoriteLogic->getUsersByFavoriteItemId($this->id);
			$isFavorite = (count($users));
		}

		//プラグインがアクティブになっていること、顧客数が一人以上いる場合に表示する
		DisplayPlugin::toggle("favorite", $isFavorite);
	}

	/**
	 * 添付ファイル取得
	 */
	private function _getAttachments(SOYShop_Item $item){
		return $item->getAttachments();
	}

	private function _getOrderCount(SOYShop_Item $item){

		if(!$this->config->getChildItemStock()) return $item->getOrderCount();

		//子商品の在庫管理設定をオン(子商品の注文数合計を取得する)
		//子商品のIDを取得する
		$ids = self::_getChildItemIds($item->getId());
		if(!count($ids)) return 0;

		$count = 0;
		$logic = SOY2Logic::createInstance("logic.order.OrderLogic");
		foreach($ids as $id){
			try{
				$count += $logic->getOrderCountByItemId($id);
			}catch(Exception $e){
				//
			}
		}
		return $count;
	}

	private function _getChildItemIds($itemId){

		$ids = array();

		$dao = new SOY2DAO();
		$sql = "select id from soyshop_item where item_type = :id";
		$binds = array(":id" => $itemId);
		try{
			$result = $dao->executeQuery($sql,$binds);
		}catch(Exception $e){
			return 0;
		}
		$ids = array();
		foreach($result as $value){
			$ids[] = $value["id"];
		}

		return $ids;
	}

	function getSubMenu(){
		try{
			return SOY2HTMLFactory::createInstance("Item.SubMenu.DetailMenuPage", array(
				"arguments" => array($this->id)
			))->getObject();
		}catch(Exception $e){
			//
			return null;
		}
	}

	function getFooterMenu(){
		try{
			return SOY2HTMLFactory::createInstance("Item.FooterMenu.DetailFooterMenuPage", array(
				"arguments" => array($this->id)
			))->getObject();
		}catch(Exception $e){
			//
			return null;
		}
	}

	function getScripts(){
		$root = SOY2PageController::createRelativeLink("./js/");
		return array(
			$root . "ImageSelect.js",
			$root . "jquery/treeview/jquery.treeview.pack.js",
			//$root . "tools/soy2_date_picker.pack.js"
			$root . "tools/datepicker-ja.js",
			$root . "tools/datepicker.js"
		);
	}

	function getCSS(){
		$root = SOY2PageController::createRelativeLink("./js/");
		return array(
			$root . "jquery/treeview/jquery.treeview.css",
			$root . "tree.css",
			//$root . "tools/soy2_date_picker.css"
		);
	}

	/**
	 * 画像のアップロード
	 *
	 * @return url
	 * 失敗時には false
	 */
	function uploadImage(){
		$item = soyshop_get_item_object($this->id);

		$urls = array();

		foreach($_FILES as $upload){
			foreach($upload["name"] as $key => $value){
				//replace invalid filename
				$upload["name"][$key] = strtolower(str_replace("%","",rawurlencode($upload["name"][$key])));

				$pathinfo = pathinfo($upload["name"][$key]);
				if(!isset($pathinfo["filename"]))$pathinfo["filename"] = str_replace("." . $pathinfo["extension"], $pathinfo["basename"]);

				//この拡張ポイントはプラグインは一つのみ使用可能
				SOYShopPlugin::load("soyshop.upload.image");
				$name = SOYShopPlugin::invoke("soyshop.upload.image", array(
					"mode" => "customfield",
					"item" => $item,
					"pathinfo" => $pathinfo
				))->getName();

				if(is_null($name) || !strlen($name)){
					$counter = 0;
					while(true){
						$name = ($counter > 0) ? $pathinfo["filename"] . "_" . $counter . "." . $pathinfo["extension"] : $pathinfo["filename"] . "." . $pathinfo["extension"];
						if(!file_exists($item->getAttachmentsPath() . $name)) break;
						$counter++;
					}
				}

				//get unique file name
				$filepath = $item->getAttachmentsPath() . $name;

				//一回でも失敗した場合はfalseを返して終了（rollbackは無し）
				$result = move_uploaded_file($upload["tmp_name"][$key], $filepath);
				@chmod($filepath,0604);

				if($result){
					$url = $item->getAttachmentsUrl() . $name;
					$urls[] = $url;
				}else{
					return false;
				}
			}
		}

		return $urls;
	}

	function getCategoryIds(){
		$categoriesDao = SOY2DAOFactory::create("shop.SOYShop_CategoriesDAO");
		try{
			$categories = $categoriesDao->getByItemId($this->id);
		}catch(Exception $e){
			$categories = array();
		}

		$array = array();
		foreach($categories as $category){
			$array[] = $category->getCategoryId();
		}

		return $array;
	}

	function getCategoriesName($obj){
		$categoryIds =$this->getCategoryIds($this->id);

		$array = array();
		foreach($categoryIds as $categoryId){
			$array[] = $obj[$categoryId]->getNameWithStatus();
		}

		return implode(",",$array);
	}

	private function _getCategoryRelation(SOYShop_Category $category){
		$dao = SOY2DAOFactory::create("shop.SOYShop_CategoryDAO");

		$array = array();

		try{
			if(isset($category)){
				$array[] = $category->getNameWithStatus();
				if(!is_null($category->getParent())){
					$parent = $dao->getById($category->getParent());
					$array[] = $parent->getNameWithStatus();
					if(!is_null($parent->getParent())){
						$grandParent = $dao->getById($parent->getParent());
						$array[] = $grandParent->getNameWithStatus();
					}
				}
			}
		}catch(Exception $e){
			//do nothing
		}

		if(array_key_exists(0, $array)){
			$text = implode(" > ",array_reverse($array));
		}else{
			$text = "選択してください";
		}

		return $text;
	}

	function getBreadcrumb(){
		return BreadcrumbComponent::build("商品詳細", array("Item" => "商品管理"));
	}
}
