<?php

class ReplacementStringConfigPage extends WebPage{

	private $pluginObj;

	function __construct(){}

	function doPost(){

		if(soy2_check_token()){

			$list = $this->pluginObj->getStringList();

			if(isset($_POST["add"])){

				$values = array();
				$values["symbol"] = trim(htmlspecialchars($_POST["symbol"], ENT_QUOTES, "UTF-8"));
				$values["string"] = trim(htmlspecialchars($_POST["string"], ENT_QUOTES, "UTF-8"));

				$list[] = $values;
				$this->pluginObj->setStringList($list);

				CMSPlugin::savePluginConfig($this->pluginObj->getId(), $this->pluginObj);
				CMSPlugin::redirectConfigPage();
			}

			if(isset($_POST["change"])){
				foreach($list as $key => $values){
					if(isset($_POST["string"][$key])){
						$values["string"] = trim(htmlspecialchars($_POST["string"][$key], ENT_QUOTES, "UTF-8"));
					}

					$list[$key] = $values;
				}

				$this->pluginObj->setStringList($list);

				if(isset($_POST["language"])){
					$_arr = array();
					foreach($_POST["language"] as $symbol => $langs){
						foreach($langs as $lang => $str){
							$str = trim($str);
							if(!strlen($str)) continue;
							if(!isset($_arr[$symbol])) $_arr[$symbol] = array();
							$_arr[$symbol][$lang] = $str;
						}
					}

					$this->pluginObj->setLanguageStringList($_arr);
				}

				CMSPlugin::savePluginConfig($this->pluginObj->getId(), $this->pluginObj);
				CMSPlugin::redirectConfigPage();
			}
		}
	}

	function execute(){
		//削除
		if(isset($_GET["remove"])){
			self::remove();
		}

		parent::__construct();
		
		$this->addForm("form");

		$list = $this->pluginObj->getStringList();
		DisplayPlugin::toggle("has_symbol_list", count($list));

		$this->addForm("change_form");

		SOY2::import("site_include.plugin.replacement_string.component.ReplacementStringListComponent");
		SOY2::import("site_include.plugin.replacement_string.component.MultiLanguageReplacementListComponent");
		$this->createAdd("string_list", "ReplacementStringListComponent", array(
			"list" => $list,
			"multiLangs" => self::_getMultiLanguageList(),
			"multiLangConf" => $this->pluginObj->getLanguageStringList()
		));
	}

	private function _getMultiLanguageList(){
		if(!CMSPlugin::activeCheck("util_multi_language")) return array();

		$langPlugin = CMSPlugin::loadPluginConfig("UtilMultiLanguagePlugin");
		if(!$langPlugin){
			SOY2::import("site_include.plugin.util_multi_language.util_multi_language", ".php");
			$langPlugin = new UtilMultiLanguagePlugin();
		}
		
		$config = $langPlugin->getConfig();
		if(!is_array($config) || !count($config)) return $config;

		$_arr = array();
		foreach($config as $lang => $cnf){
			if($lang == "jp") continue;
			if(!isset($cnf["is_use"]) || (int)$cnf["is_use"] !== 1) continue;
			$_arr[] = $lang;
		}

		return $_arr;
	}

	private function remove(){
		$list = $this->pluginObj->getStringList();
		if(isset($list[$_GET["remove"]])){
			unset($list[$_GET["remove"]]);
			//要素を詰める
			$array = array();
			if(count($list)){
				foreach($list as $values){
					$array[] = $values;
				}
			}

			$this->pluginObj->setStringList($array);
			CMSPlugin::savePluginConfig($this->pluginObj->getId(), $this->pluginObj);
			header("Location:" . SOY2PageController::createRelativeLink($_SERVER["PHP_SELF"], true) . "?replacement_string#config");
			exit;
		}
	}

	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}
