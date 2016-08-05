<?php
class CommonItemOptionConfigFormPage extends WebPage{

	private $types = array("select" => "セレクトボックス", "radio" => "ラジオボタン");

    function __construct() {
    	SOY2DAOFactory::importEntity("SOYShop_DataSets");
    }
    
    function doPost(){
    	$logic = SOY2Logic::createInstance("module.plugins.common_item_option.logic.ItemOptionLogic");
    	
    	if(isset($_POST["create"])){
			
			$array = $logic->getOptions();
			
			$obj["name"] = $_POST["option_new_name"];
			$obj["type"] = $_POST["option_type"];
			
			$array[$_POST["option_id"]] = $obj;
			
			SOYShop_DataSets::put("item_option", soy2_serialize($array));
			
			SOY2PageController::jump("Config.Detail?plugin=common_item_option&updated=created");
			
		}
		
		//update
		if(isset($_POST["update_submit"])){
			$optionId = $_POST["update_submit"];

			$array = $logic->getOptions();
			$array[$optionId]["name"] = $_POST["obj"]["name"];
			$array[$optionId]["type"] = $_POST["obj"]["type"];
			
			SOYShop_DataSets::put("item_option", soy2_serialize($array));
		}
		
		//advanced config
		if(isset($_POST["update_advance"])){
			$optionId = $_POST["update_advance"];
			
			$array = $logic->getOptions();
			foreach($_POST["Option"] as $key => $value){
				$array[$optionId][$key] = $value;
			}
			
			SOYShop_DataSets::put("item_option", soy2_serialize($array));
		}
		
		//delete
		if(isset($_POST["delete_submit"])){
			$optionId = $_POST["delete_submit"];

			$array = $logic->getOptions();
			$array[$optionId] = null;
			unset($array[$optionId]);
			
			SOYShop_DataSets::put("item_option", soy2_serialize($array));
		}
		
		//move
		if(isset($_POST["move_up"]) || isset($_POST["move_down"])){
			$optionId = $_POST["option_id"];

			$configs = SOYShop_DataSets::get("item_option", null);
			if(is_null($configs)) SOY2PageController::jump("Config.Detail?plugin=common_item_option&failed");
			$configs = soy2_unserialize($configs);
			
			$keys = array_keys($configs);
			$currentKey = array_search($optionId, $keys);
			$swap = (isset($_POST["move_up"])) ? $currentKey - 1 :$currentKey + 1;

			if($swap >= 0 && $swap < count($keys)){
				$tmp = $keys[$currentKey];
				$keys[$currentKey] = $keys[$swap];
				$keys[$swap] = $tmp;

				$tmpArray = array();
				foreach($keys as $index => $value){
					$option = $configs[$value];
					$tmpArray[$value] = $option;
				}

				SOYShop_DataSets::put("item_option", soy2_serialize($tmpArray));
			}
		}

		SOY2PageController::jump("Config.Detail?plugin=common_item_option&updated");
    }
    
    function execute(){
    	WebPage::WebPage();
    	
    	$this->addForm("create_form");
    	
    	$this->addModel("updated", array(
			"visible" => (isset($_GET["updated"]))
		));
		
		$this->addModel("error", array(
			"visible" => (isset($_GET["error"]))
		));
		
		$logic = SOY2Logic::createInstance("module.plugins.common_item_option.logic.ItemOptionLogic");
		
		$this->addSelect("option_type_select", array(
			"options" => $logic->getTypes(),
			"name" => "option_type"
		));
		
		SOY2::import("util.SOYShopPluginUtil");
		SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");
		SOY2::imports("module.plugins.common_item_option.component.*");
		$this->createAdd("option_list", "OptionListComponent", array(
			"list" => $logic->getOptions(),
			"types" => $this->types,
			"languages" => self::getLanguageConfig(),	//多言語の設定を持っていく
			"installedLangPlugin" => SOYShopPluginUtil::checkIsActive("util_multi_language")
		));
    }
    
    private function getLanguageConfig(){
    	SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");
		$conf = UtilMultiLanguageUtil::allowLanguages();
		unset($conf[UtilMultiLanguageUtil::LANGUAGE_JP]);
		
		return $conf;
    }

    function setConfigObj($obj) {
		$this->config = $obj;
	}
}
?>