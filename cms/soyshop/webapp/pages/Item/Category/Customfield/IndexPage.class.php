<?php

class IndexPage extends WebPage{

    function doPost(){

        if(isset($_POST["create"])){
            $dao = SOY2DAOFactory::create("shop.SOYShop_CategoryAttributeDAO");
            $configs = SOYShop_CategoryAttributeConfig::load();

            $custom_id = $_POST["custom_id"];

            //多言語化プラグインとバッティングしない様に
            if(!preg_match('/^category_name_(.*)/', $custom_id)){
                $config = new SOYShop_CategoryAttributeConfig();
                $config->setLabel($_POST["custom_new_name"]);
                $config->setFieldId($custom_id);
                $config->setType($_POST["custom_type"]);

                $configs[] = $config;

                SOYShop_CategoryAttributeConfig::save($configs);
                SOY2PageController::jump("Item.Category.Customfield?updated=created");
            }
        }

        //update
        if(isset($_POST["update_submit"])){
            $fieldId = $_POST["update_submit"];

            $dao = SOY2DAOFactory::create("shop.SOYShop_CategoryAttributeDAO");
            $configs = SOYShop_CategoryAttributeConfig::load(true);

            $config = $configs[$fieldId];
            SOY2::cast($config, (object)$_POST["obj"]);

            SOYShop_CategoryAttributeConfig::save($configs);
        }

        //advanced config
        if(isset($_POST["update_advance"])){
            $fieldId = $_POST["update_advance"];

            $dao = SOY2DAOFactory::create("shop.SOYShop_CategoryAttributeDAO");
            $configs = SOYShop_CategoryAttributeConfig::load(true);

            $config = $configs[$fieldId];
            $config->setConfig($_POST["config"]);

            SOYShop_CategoryAttributeConfig::save($configs);
        }

        //delete
        if(isset($_POST["delete_submit"])){
            $fieldId = $_POST["delete_submit"];

            $dao = SOY2DAOFactory::create("shop.SOYShop_CategoryAttributeDAO");
            $configs = SOYShop_CategoryAttributeConfig::load(true);

            unset($configs[$fieldId]);

            SOYShop_CategoryAttributeConfig::save($configs);
        }

        //move
        if(isset($_POST["move_up"]) || isset($_POST["move_down"])){
            $fieldId = $_POST["field_id"];

            $dao = SOY2DAOFactory::create("shop.SOYShop_CategoryAttributeDAO");
            $configs = SOYShop_CategoryAttributeConfig::load(true);

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

                SOYShop_CategoryAttributeConfig::save($tmpArray);
            }
        }

        SOY2PageController::jump("Item.Category.Customfield?updated");
    }

    function __construct() {
        parent::__construct();

        DisplayPlugin::toggle("error", isset($_GET["error"]));

        $this->addForm("create_form");

        $dao = SOY2DAOFactory::create("shop.SOYShop_CategoryAttributeDAO");
        $config = SOYShop_CategoryAttributeConfig::load();

        $types = SOYShop_CategoryAttributeConfig::getTypes();
        $this->addSelect("custom_type_select", array(
            "options" => $types,
            "name" => "custom_type"
        ));

        $this->createAdd("field_list", "_common.Category.FieldListComponent", array(
            "list" => $config,
            "types" => $types
        ));
    }

	function getBreadcrumb(){
		return BreadcrumbComponent::build("カスタム項目管理", array("Item" => "商品管理", "Item.Category" => "カテゴリ管理"));
	}

	function getFooterMenu(){
		try{
			return SOY2HTMLFactory::createInstance("Item.FooterMenu.CategoryFooterMenuPage")->getObject();
		}catch(Exception $e){
			//
			return null;
		}
	}
}
