<?php

class CommonOrderCustomfieldConfigFormPage extends WebPage{

    function __construct() {
    	SOY2DAOFactory::importEntity("SOYShop_DataSets");
    }

    function doPost(){

    	$dao = SOY2DAOFactory::create("order.SOYShop_OrderAttributeDAO");

    	if(isset($_POST["create"])){

			$configs = SOYShop_OrderAttributeConfig::load();
			$custom_id = $_POST["custom_id"];

			$config = new SOYShop_OrderAttributeConfig();
			$config->setLabel($_POST["custom_new_name"]);
			$config->setFieldId($custom_id);
			$config->setType($_POST["custom_type"]);

			$configs[] = $config;

			SOYShop_OrderAttributeConfig::save($configs);
			SOY2PageController::jump("Config.Detail?plugin=common_order_customfield&updated=created");

		}

		//update
		if(isset($_POST["update_submit"])){
			$fieldId = $_POST["update_submit"];

			$configs = SOYShop_OrderAttributeConfig::load(true);
			$config = $configs[$fieldId];
			SOY2::cast($config, (object)$_POST["obj"]);

			SOYShop_OrderAttributeConfig::save($configs);
		}

		//advanced config
		if(isset($_POST["update_advance"])){
			$fieldId = $_POST["update_advance"];

			$configs = SOYShop_OrderAttributeConfig::load(true);
			$config = $configs[$fieldId];
			$value = self::_checkValidate($_POST["config"]);
			$config->setConfig($value);

			SOYShop_OrderAttributeConfig::save($configs);
		}

		//delete
		if(isset($_POST["delete_submit"])){
			$fieldId = $_POST["delete_submit"];

			$configs = SOYShop_OrderAttributeConfig::load(true);
			unset($configs[$fieldId]);

			SOYShop_OrderAttributeConfig::save($configs);
		}

		//move
		if(isset($_POST["move_up"]) || isset($_POST["move_down"])){
			$fieldId = $_POST["field_id"];

			$configs = SOYShop_OrderAttributeConfig::load(true);

			$keys = array_keys($configs);
			$currentKey = array_search($fieldId, $keys);
			$swap = (isset($_POST["move_up"])) ? $currentKey - 1 :$currentKey + 1;

			if($swap >= 0 && $swap < count($keys)){
				$tmp = $keys[$currentKey];
				$keys[$currentKey] = $keys[$swap];
				$keys[$swap] = $tmp;

				$tmpArray = array();
				foreach($keys as $index => $value){
					$field = $configs[$value];
					$tmpArray[$field->getFieldId()] = $field;
				}

				SOYShop_OrderAttributeConfig::save($tmpArray);
			}
		}

		SOY2PageController::jump("Config.Detail?plugin=common_order_customfield&updated");
    }

    private function _checkValidate($value){
		$value["orderSearchItem"] = (isset($value["orderSearchItem"])) ? 1 : 0;
    	$value["attributeOther"] = (isset($value["attributeOther"])) ? 1 : 0;
    	return $value;
    }

    function execute(){
    	parent::__construct();

    	DisplayPlugin::toggle("error", isset($_GET["error"]));

		$this->addForm("create_form");

		$dao = SOY2DAOFactory::create("order.SOYShop_OrderAttributeDAO");
		$config = SOYShop_OrderAttributeConfig::load();

		$types = SOYShop_OrderAttributeConfig::getTypes();
		$this->addSelect("custom_type_select", array(
			"options" => $types,
			"name" => "custom_type"
		));

		SOY2::import("module.plugins.common_order_customfield.component.FieldListComponent");
		$this->createAdd("field_list", "FieldListComponent", array(
			"list" => $config,
			"types" => $types
		));
    }

    function setConfigObj($obj) {
		$this->config = $obj;
	}
}
