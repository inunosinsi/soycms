<?php

class CreatePage extends WebPage{

	function doPost(){
		if(!AUTH_OPERATE) return;	//操作権限がないアカウントの場合は以後のすべての動作を封じる

		if(isset($_POST["Item"]) && soy2_check_token()){
			$item = (object)$_POST["Item"];

			$dao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
			$logic = SOY2Logic::createInstance("logic.shop.item.ItemLogic");

			$item = SOY2::cast("SOYShop_Item",$item);
			if(is_null($item->getStock())) $item->setStock(0);
			$item->setType($_POST["ItemType"]);

			//
			if($item->getType() == SOYShop_Item::TYPE_CHILD){
				if(isset($_POST["group_item_id"]))$item->setType($_POST["group_item_id"]);
			}

			if($item->getType() == SOYShop_Item::TYPE_DOWNLOAD){
				$dir = SOYSHOP_SITE_DIRECTORY . "download/" . $item->getCode() . "/";
				if(!file_exists($dir)){
					mkdir($dir, 0777, true);

					//.htaccessを作成する
					file_put_contents($dir.".htaccess","deny from all");
					//index.html
					file_put_contents($dir."index.html","<!-- empty -->");
				}
			}

			if($item->getType() == SOYShop_Item::TYPE_DOWNLOAD_CHILD){
				if(isset($_POST["dlgroup_item_id"]))$item->setType($_POST["dlgroup_item_id"]);
			}

			if($logic->validate($item)){

				$id = $logic->create($item);
				$item->setId($id);

				SOYShopPlugin::load("soyshop.item.name");
				SOYShopPlugin::invoke("soyshop.item.name", array(
					"item" => $item
				));

				SOY2PageController::jump("Item.Detail.$id?updated=created");
				exit;
			}


			$this->obj = $item;
			$this->errors = $logic->getErrors();
		}
	}

	var $obj;
	var $errors = array();

    function __construct() {
		MessageManager::addMessagePath("admin");

    	parent::__construct();

    	self::_buildForm();
    }

    private function _buildForm(){

		$this->addForm("create_form");

		$dao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		$obj = ($this->obj) ? $this->obj : new SOYShop_Item();

		if(isset($_GET["parent"])){
			$obj->setType($_GET["parent"]);
		}

		if(isset($_GET["dlparent"])){
			$obj->setType($_GET["dlparent"]);
		}

		$this->addInput("item_name", array(
    		"name" => "Item[name]",
    		"value" => $obj->getName()
    	));

    	SOYShopPlugin::load("soyshop.item.name");
		$nameForm = SOYShopPlugin::display("soyshop.item.name", array(
			"item" => $obj
		));

		$this->addLabel("extension_item_name_input", array(
			"html" => $nameForm
		));

    	$this->addInput("item_code", array(
    		"name" => "Item[code]",
    		"value" => $obj->getCode()
    	));

		$cnf = SOYShop_ShopConfig::load();
		$isIgnoreStock = ($cnf->getIgnoreStock() && $cnf->getIsHiddenStockCount());
		DisplayPlugin::toggle("item_stock", !$isIgnoreStock);
    	$this->addInput("item_stock", array(
    		"name" => "Item[stock]",
    		"value" => $obj->getStock(),
			"readonly" => (SOYShopPluginUtil::checkIsActive("reserve_calendar"))
    	));

		$this->addInput("item_unit", array(
			"name" => "Item[unit]",
			"value" => (!is_null($obj->getUnit())) ? $obj->getUnit() : SOYShop_Item::UNIT,
			"style" => "width:80px"
		));

    	$this->addInput("item_price", array(
    		"name" => "Item[price]",
    		"value" => $obj->getPrice()
    	));

		SOY2::import("domain.config.SOYShop_ShopConfig");
		DisplayPlugin::toggle("item_description", $cnf->getDisplayItemDescription());

		$config = $obj->getConfigObject();
    	$this->addTextArea("item_description", array(
    		"name" => "Item[config][description]",
    		"value" => (isset($config["description"])) ? $config["description"] : ""
    	));

		$categories = soyshop_get_category_objects();

		$this->createAdd("category_tree", "_base.MyTreeComponent", array(
			"list" => $categories,
			"selected" => $obj->getCategory()
		));

		$this->addInput("item_category", array(
			"name" => "Item[category]",
			"value" => $obj->getCategory(),
			"attr:id" => "item_category"
		));

		$this->addLabel("item_category_text", array(
			"text" => (isset($categories[$obj->getCategory()])) ? $categories[$obj->getCategory()]->getName() : "選択してください",
			"attr:id" => "item_category_text"
		));

		/*
		 * グループ周り
		 */
		if(is_numeric($obj->getType())){
			$itemType = (isset($_GET["parent"])) ? SOYShop_Item::TYPE_CHILD : SOYShop_Item::TYPE_DOWNLOAD_CHILD;
		}else{
			$itemType = $obj->getType();
		}
		$this->addInput("item_type_hidden", array(
			"name" => "ItemType",
			"value" => $itemType
		));
		$this->addCheckBox("radio_type_normal", array(
			"elementId" => "radio_type_normal",
			"name" => "item_type",
			"value" => SOYShop_Item::TYPE_SINGLE,
			"selected" => ($obj->getType() == SOYShop_Item::TYPE_SINGLE),
			"onclick" => '$(\'#item_type_hidden\').val("' . SOYShop_Item::TYPE_SINGLE . '");'
		));
		$this->addCheckBox("radio_type_group", array(
			"elementId" => "radio_type_group",
			"name" => "item_type",
			"value" => SOYShop_Item::TYPE_GROUP,
			"selected" => ($obj->getType() == SOYShop_Item::TYPE_GROUP),
			"onclick" => '$(\'#item_type_hidden\').val("' . SOYShop_Item::TYPE_GROUP . '");'
		));
		$this->addCheckBox("radio_type_child", array(
			"elementId" => "radio_type_child",
			"name" => "item_type",
			"value" => SOYShop_Item::TYPE_CHILD,
			"selected" => ($itemType == SOYShop_Item::TYPE_CHILD),
			"onclick" => '$(\'#item_type_hidden\').val("' . SOYShop_Item::TYPE_CHILD . '");$(\'#group_item_div\').show();'
		));
		$this->addCheckBox("radio_type_download", array(
			"elementId" => "radio_type_download",
			"name" => "item_type",
			"value" => SOYShop_Item::TYPE_DOWNLOAD,
			"selected" => ($itemType == SOYShop_Item::TYPE_DOWNLOAD),
			"onclick" => '$(\'#item_type_hidden\').val("' . SOYShop_Item::TYPE_DOWNLOAD . '");'
		));
		$this->addCheckBox("radio_type_dlgroup", array(
			"elementId" => "radio_type_dlgroup",
			"name" => "item_type",
			"value" => SOYShop_Item::TYPE_DOWNLOAD_GROUP,
			"selected" => ($itemType == SOYShop_Item::TYPE_DOWNLOAD_GROUP),
			"onclick" => '$(\'#item_type_hidden\').val("' . SOYShop_Item::TYPE_DOWNLOAD_GROUP . '");'
		));
		$this->addCheckBox("radio_type_dlgroup_child", array(
			"elementId" => "radio_type_dlgroup_child",
			"name" => "item_type",
			"value" => SOYShop_Item::TYPE_DOWNLOAD_CHILD,
			"selected" => ($itemType == SOYShop_Item::TYPE_DOWNLOAD_CHILD),
			"onclick" => '$(\'#item_type_hidden\').val("' . SOYShop_Item::TYPE_DOWNLOAD_CHILD . '");$(\'#dlgroup_item_div\').show();'
		));

		$groupItems = $dao->getByType(SOYShop_Item::TYPE_GROUP);

		$this->addSelect("group_item_select", array(
			"name" => "group_item_id",
			"options" => $groupItems,
			"property" => "name",
			"selected" => $obj->getType()
		));

		DisplayPlugin::toggle("group_item_exists", (count($groupItems) > 0));

		//ダウンロード販売プラグインがアクティブの時に表示
		DisplayPlugin::toggle("download_exists", SOYShopPluginUtil::checkIsActive("download_assistant"));

		$dlgroupItems = $dao->getByType(SOYShop_Item::TYPE_DOWNLOAD_GROUP);

		$this->addSelect("dlgroup_item_select", array(
			"name" => "dlgroup_item_id",
			"options" => $dlgroupItems,
			"property" => "name",
			"selected" => $obj->getType()
		));

		DisplayPlugin::toggle("dlgroup_item_exists", (count($dlgroupItems) > 0));

    	//error
		foreach(array("name","code") as $key){
			$this->addLabel("error_$key", array(
				"text" => (isset($this->errors[$key])) ? $this->errors[$key] : "",
				"visible" => (isset($this->errors[$key]) && strlen($this->errors[$key]))
			));
		}
    }

	function getBreadcrumb(){
		return BreadcrumbComponent::build("商品の追加", array("Item" => "商品管理"));
	}

    function getScripts(){
		$root = SOY2PageController::createRelativeLink("./js/");
		return array(
			$root . "jquery/treeview/jquery.treeview.pack.js",
		);
	}

	function getCSS(){
		$root = SOY2PageController::createRelativeLink("./js/");
		return array(
			$root . "jquery/treeview/jquery.treeview.css",
			$root . "tree.css",
		);
	}
}
