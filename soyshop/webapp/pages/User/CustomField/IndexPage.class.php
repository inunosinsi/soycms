<?php

class IndexPage extends WebPage{

	function doPost(){

		if(isset($_POST["create"])){
			$dao = soyshop_get_hash_table_dao("user_attribute");
			$cfgs = SOYShop_UserAttributeConfig::load();

			$custom_id = $_POST["custom_id"];

			$cfg = new SOYShop_UserAttributeConfig();
			$cfg->setLabel($_POST["custom_new_name"]);
			$cfg->setFieldId($custom_id);
			$cfg->setType($_POST["custom_type"]);

			$cfgs[] = $cfg;

			SOYShop_UserAttributeConfig::save($cfgs);
			SOY2PageController::jump("User.CustomField?updated=created");
		}

		//update
		if(isset($_POST["update_submit"])){
			$fieldId = $_POST["update_submit"];

			$dao = soyshop_get_hash_table_dao("user_attribute");
			$cfgs = SOYShop_UserAttributeConfig::load(true);

			$cfg = $cfgs[$fieldId];
			$cfgs[$fieldId] = SOY2::cast($cfg,(object)$_POST["obj"]);

			SOYShop_UserAttributeConfig::save($cfgs);
		}

		//advanced config
		if(isset($_POST["update_advance"])){
			$fieldId = $_POST["update_advance"];

			$dao = SOY2DAOFactory::create("user.SOYShop_UserAttributeDAO");
			$cfgs = SOYShop_UserAttributeConfig::load(true);

			$cfg = $cfgs[$fieldId];
			$cfg->setConfig($_POST["config"]);
			$cfgs[$fieldId] = $cfg;

			SOYShop_UserAttributeConfig::save($cfgs);
		}

		//delete
		if(isset($_POST["delete_submit"])){
			$fieldId = $_POST["delete_submit"];

			$dao = SOY2DAOFactory::create("user.SOYShop_UserAttributeDAO");
			$cfgs = SOYShop_UserAttributeConfig::load(true);

			unset($cfgs[$fieldId]);

			SOYShop_UserAttributeConfig::save($cfgs);
		}

		//move
		if(isset($_POST["move_up"]) || isset($_POST["move_down"])){
			$fieldId = $_POST["field_id"];

			$dao = SOY2DAOFactory::create("user.SOYShop_UserAttributeDAO");
			$cfgs = SOYShop_UserAttributeConfig::load(true);

			$keys = array_keys($cfgs);
			$currentKey = array_search($fieldId,$keys);
			$swap = (isset($_POST["move_up"])) ? $currentKey-1 :$currentKey+1;

			if($swap >= 0 && $swap < count($keys)){
				$tmp = $keys[$currentKey];
				$keys[$currentKey] = $keys[$swap];
				$keys[$swap] = $tmp;

				$tmpArray = array();
				foreach($keys as $index => $value){
					$field = $cfgs[$value];
					$tmpArray[$field->getFieldId()] = $field;
				}

				SOYShop_UserAttributeConfig::save($tmpArray);
			}

		}

		SOY2PageController::jump("User.CustomField?updated");
	}

    function __construct() {
    	//利用権限があるか
    	if(!class_exists("SOYShopPluginUtil") || !SOYShopPluginUtil::checkIsActive("common_user_customfield")){
    		SOY2PageController::jump("User");
    	}


    	parent::__construct();

		foreach(array("updated", "error") as $typ){
			DisplayPlugin::toggle($typ, isset($_GET[$typ]));
		}

		$this->addForm("create_form");

    	$dao = SOY2DAOFactory::create("user.SOYShop_UserAttributeDAO");
		$cfgs = SOYShop_UserAttributeConfig::load();

    	$types = SOYShop_UserAttributeConfig::getTypes();
		$this->addSelect("custom_type_select", array(
			"options" => $types,
			"name" => "custom_type"
		));

    	$this->createAdd("field_list", "_common.User.FieldListComponent", array(
			"list" => $cfgs,
			"types" => $types
		));
    }

	function getBreadcrumb(){
		return BreadcrumbComponent::build("ユーザカスタム項目管理", array("User" => SHOP_USER_LABEL . "管理"));
	}

	function getFooterMenu(){
		try{
			return SOY2HTMLFactory::createInstance("User.FooterMenu.UserFooterMenuPage")->getObject();
		}catch(Exception $e){
			//
			return null;
		}
	}
}
