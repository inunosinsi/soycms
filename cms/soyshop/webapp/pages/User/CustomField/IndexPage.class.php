<?php

class IndexPage extends WebPage{

	function doPost(){

		if(isset($_POST["create"])){
			$dao = SOY2DAOFactory::create("user.SOYShop_UserAttributeDAO");
			$configs = SOYShop_UserAttributeConfig::load();

			$custom_id = $_POST["custom_id"];

			$config = new SOYShop_UserAttributeConfig();
			$config->setLabel($_POST["custom_new_name"]);
			$config->setFieldId($custom_id);
			$config->setType($_POST["custom_type"]);

			$configs[] = $config;

			SOYShop_UserAttributeConfig::save($configs);
			SOY2PageController::jump("User.CustomField?updated=created");
		}

		//update
		if(isset($_POST["update_submit"])){
			$fieldId = $_POST["update_submit"];

			$dao = SOY2DAOFactory::create("user.SOYShop_UserAttributeDAO");
			$configs = SOYShop_UserAttributeConfig::load(true);

			$config = $configs[$fieldId];
			SOY2::cast($config,(object)$_POST["obj"]);

			SOYShop_UserAttributeConfig::save($configs);
		}

		//advanced config
		if(isset($_POST["update_advance"])){
			$fieldId = $_POST["update_advance"];

			$dao = SOY2DAOFactory::create("user.SOYShop_UserAttributeDAO");
			$configs = SOYShop_UserAttributeConfig::load(true);

			$config = $configs[$fieldId];
			$config->setConfig($_POST["config"]);

			SOYShop_UserAttributeConfig::save($configs);
		}

		//delete
		if(isset($_POST["delete_submit"])){
			$fieldId = $_POST["delete_submit"];

			$dao = SOY2DAOFactory::create("user.SOYShop_UserAttributeDAO");
			$configs = SOYShop_UserAttributeConfig::load(true);

			unset($configs[$fieldId]);

			SOYShop_UserAttributeConfig::save($configs);
		}

		//move
		if(isset($_POST["move_up"]) || isset($_POST["move_down"])){
			$fieldId = $_POST["field_id"];

			$dao = SOY2DAOFactory::create("user.SOYShop_UserAttributeDAO");
			$configs = SOYShop_UserAttributeConfig::load(true);

			$keys = array_keys($configs);
			$currentKey = array_search($fieldId,$keys);
			$swap = (isset($_POST["move_up"])) ? $currentKey-1 :$currentKey+1;

			if($swap >= 0 && $swap < count($keys)){
				$tmp = $keys[$currentKey];
				$keys[$currentKey] = $keys[$swap];
				$keys[$swap] = $tmp;

				$tmpArray = array();
				foreach($keys as $index => $value){
					$field = $configs[$value];
					$tmpArray[$field->getFieldId()] = $field;
				}

				SOYShop_UserAttributeConfig::save($tmpArray);
			}

		}

		SOY2PageController::jump("User.CustomField?updated");
	}

    function __construct() {
    	//利用権限があるか
    	$correct = class_exists("SOYShopPluginUtil") && (SOYShopPluginUtil::checkIsActive("common_user_customfield"));
    	if(!$correct){
    		SOY2PageController::jump("User");
    	}


    	parent::__construct();

    	$this->addModel("updated", array(
			"visible" => (isset($_GET["updated"]))
		));

		$this->addModel("error", array(
			"visible" => (isset($_GET["error"]))
		));

		$this->addForm("create_form");

    	$dao = SOY2DAOFactory::create("user.SOYShop_UserAttributeDAO");
		$config = SOYShop_UserAttributeConfig::load();

    	$types = SOYShop_UserAttributeConfig::getTypes();
		$this->addSelect("custom_type_select", array(
			"options" => $types,
			"name" => "custom_type"
		));

    	$this->createAdd("field_list", "_common.User.FieldListComponent", array(
			"list" => $config,
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
