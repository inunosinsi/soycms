<?php
class CommonItemOptionConfigFormPage extends WebPage{

    function __construct() {
    	SOY2DAOFactory::importEntity("SOYShop_DataSets");
		SOY2::import("module.plugins.common_item_option.util.ItemOptionUtil");
    }

    function doPost(){

    	if(isset($_POST["create"])){
			$opts = ItemOptionUtil::getOptions();

			$v["name"] = $_POST["option_new_name"];
			$v["type"] = $_POST["option_type"];

			$opts[$_POST["option_id"]] = $v;
			ItemOptionUtil::saveOptions($opts);

			SOY2PageController::jump("Config.Detail?plugin=common_item_option&updated=created");
		}

		//update
		if(isset($_POST["update_submit"])){
			$optionId = $_POST["update_submit"];

			$opts = ItemOptionUtil::getOptions();
			$opts[$optionId]["name"] = $_POST["obj"]["name"];
			$opts[$optionId]["type"] = $_POST["obj"]["type"];

			ItemOptionUtil::saveOptions($opts);
		}

		//advanced config
		if(isset($_POST["update_advance"])){
			$optionId = $_POST["update_advance"];

			$opts = ItemOptionUtil::getOptions();
			foreach($_POST["Option"] as $key => $value){
				$opts[$optionId][$key] = $value;
			}

			ItemOptionUtil::saveOptions($opts);
		}

		//delete
		if(isset($_POST["delete_submit"])){
			$optionId = $_POST["delete_submit"];

			$opts = ItemOptionUtil::getOptions();
			$opts[$optionId] = null;
			unset($opts[$optionId]);

			ItemOptionUtil::saveOptions($opts);
		}

		//move
		if(isset($_POST["move_up"]) || isset($_POST["move_down"])){
			$optionId = $_POST["option_id"];

			$opts = ItemOptionUtil::getOptions();
			if(!count($opts)) SOY2PageController::jump("Config.Detail?plugin=common_item_option&failed");

			$keys = array_keys($opts);
			$currentKey = array_search($optionId, $keys);
			$swap = (isset($_POST["move_up"])) ? $currentKey - 1 :$currentKey + 1;

			if($swap >= 0 && $swap < count($keys)){
				$tmp = $keys[$currentKey];
				$keys[$currentKey] = $keys[$swap];
				$keys[$swap] = $tmp;

				$tmps = array();
				foreach($keys as $index => $value){
					$opt = $opts[$value];
					$tmps[$value] = $opt;
				}

				ItemOptionUtil::saveOptions($tmps);
			}
		}

		SOY2PageController::jump("Config.Detail?plugin=common_item_option&updated");
    }

    function execute(){
    	parent::__construct();

		DisplayPlugin::toggle("error", isset($_GET["error"]));

    	$this->addForm("create_form");

		$this->addSelect("option_type_select", array(
			"options" => ItemOptionUtil::getTypes(),
			"name" => "option_type"
		));

		SOY2::import("util.SOYShopPluginUtil");
		SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");
		SOY2::imports("module.plugins.common_item_option.component.*");
		$this->createAdd("option_list", "OptionListComponent", array(
			"list" => ItemOptionUtil::getOptions(),
			"languages" => self::getLanguageConfig(),	//多言語の設定を持っていく
			"installedLangPlugin" => SOYShopPluginUtil::checkIsActive("util_multi_language")
		));
    }

	private function getLanguageConfig(){
		SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");
		if(class_exists("UtilMultiLanguageUtil")){
			$conf = UtilMultiLanguageUtil::allowLanguages();
			unset($conf[UtilMultiLanguageUtil::LANGUAGE_JP]);
			return $conf;
		}else{
			return array();
		}
	}

    function setConfigObj($obj) {
		$this->config = $obj;
	}
}
