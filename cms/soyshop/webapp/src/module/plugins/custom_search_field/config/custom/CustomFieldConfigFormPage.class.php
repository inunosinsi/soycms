<?php

class CustomFieldConfigFormPage extends WebPage{

    private $configObj;

    function __construct(){
		SOY2::import("module.plugins.custom_search_field.domain.SOYShop_CustomSearchAttributeDAO");
    }

    function doPost(){
        if(isset($_POST["create"])){
            $dao = SOY2DAOFactory::create("SOYShop_CustomSearchAttributeDAO");
            $configs = SOYShop_CustomSearchAttributeConfig::load();

            $custom_id = $_POST["custom_id"];

            //多言語化プラグインとバッティングしない様に
            if(!preg_match('/^category_name_(.*)/', $custom_id)){
                $config = new SOYShop_CustomSearchAttributeConfig();
                $config->setLabel($_POST["custom_new_name"]);
                $config->setFieldId($custom_id);
                $config->setType($_POST["custom_type"]);

                $configs[] = $config;

                SOYShop_CustomSearchAttributeConfig::save($configs);
                SOY2PageController::jump("Config.Detail?plugin=custom_search_field&custom&updated");
            }
        }

        //update
        if(isset($_POST["update_submit"])){
            $fieldId = $_POST["update_submit"];

            $dao = SOY2DAOFactory::create("SOYShop_CustomSearchAttributeDAO");
            $configs = SOYShop_CustomSearchAttributeConfig::load(true);

            $config = $configs[$fieldId];
            SOY2::cast($config, (object)$_POST["obj"]);

            SOYShop_CustomSearchAttributeConfig::save($configs);
        }

        //advanced config
        if(isset($_POST["update_advance"])){
            $fieldId = $_POST["update_advance"];

            $dao = SOY2DAOFactory::create("SOYShop_CustomSearchAttributeDAO");
            $configs = SOYShop_CustomSearchAttributeConfig::load(true);

            $config = $configs[$fieldId];
            $config->setConfig($_POST["config"]);

            SOYShop_CustomSearchAttributeConfig::save($configs);
        }

        //delete
        if(isset($_POST["delete_submit"])){
            $fieldId = $_POST["delete_submit"];

            $dao = SOY2DAOFactory::create("SOYShop_CustomSearchAttributeDAO");
            $configs = SOYShop_CustomSearchAttributeConfig::load(true);

            unset($configs[$fieldId]);

            SOYShop_CustomSearchAttributeConfig::save($configs);
        }

        //move
        if(isset($_POST["move_up"]) || isset($_POST["move_down"])){
            $fieldId = $_POST["field_id"];

            $dao = SOY2DAOFactory::create("SOYShop_CustomSearchAttributeDAO");
            $configs = SOYShop_CustomSearchAttributeConfig::load(true);

            $keys = array_keys($configs);
            $currentKey = array_search($fieldId, $keys);
            $swap = (isset($_POST["move_up"])) ? $currentKey - 1 : $currentKey + 1;

            if($swap >= 0 && $swap < count($keys)){
                $tmp = $keys[$currentKey];
                $keys[$currentKey] = $keys[$swap];
                $keys[$swap] = $tmp;

                $tmpArray = array();
                foreach($keys as $index => $value){
                    $field = $configs[$value];
                    $tmpArray[$field->getFieldId()] = $field;
                }

                SOYShop_CustomSearchAttributeConfig::save($tmpArray);
            }
        }

        SOY2PageController::jump("Config.Detail?plugin=custom_search_field&custom&updated");
	}

    function execute(){
        parent::__construct();

		$this->addLabel("nav", array(
			"html" => LinkNaviAreaComponent::build()
		));

		DisplayPlugin::toggle("error", isset($_GET["error"]));

        $this->addForm("create_form");

        $dao = SOY2DAOFactory::create("SOYShop_CustomSearchAttributeDAO");
        $config = SOYShop_CustomSearchAttributeConfig::load();

        $types = SOYShop_CustomSearchAttributeConfig::getTypes();
        $this->addSelect("custom_type_select", array(
            "options" => $types,
            "name" => "custom_type"
        ));

        $this->createAdd("field_list", "_common.Category.FieldListComponent", array(
            "list" => $config,
            "types" => $types
        ));

        DisplayPlugin::toggle("custom_plugin", SOYShopPluginUtil::checkIsActive("common_category_customfield"));
    }

    function setConfigObj($configObj){
        $this->configObj = $configObj;
    }
}
