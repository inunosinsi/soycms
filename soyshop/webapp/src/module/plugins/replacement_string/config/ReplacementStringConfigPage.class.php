<?php

class ReplacementStringConfigPage extends WebPage {

	private $configObj;
	private $langs = array();

	function __construct(){
		SOY2::import("module.plugins.replacement_string.util.ReplacementStringUtil");
		SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");	//多言語化
		
		if(SOYShopPluginUtil::checkIsActive("util_multi_language")){
			foreach(UtilMultiLanguageUtil::allowLanguages() as $lang => $_dust){
				if($lang == "jp") continue;
				$this->langs[] = $lang;
			}
		}
	}

	function doPost(){
		if(soy2_check_token()){
			$list = ReplacementStringUtil::getConfig();
			
			if(isset($_POST["add"])){
				$values = array();

				foreach(array("symbol", "string") as $idx){
					$values[$idx] = trim(htmlspecialchars($_POST[$idx], ENT_QUOTES, "UTF-8"));
				}

				$list[] = $values;
			}

			if(isset($_POST["change"])){
				//多言語サイトに対応する
				$types = array("string");
				if(count($this->langs)){
					foreach($this->langs as $lang){
						$types[] = $lang;
					}
				}

				foreach($list as $key => $values){
					foreach($types as $typ){
						if(!isset($_POST[$typ][$key])) continue;
						$values[$typ] = trim(htmlspecialchars($_POST[$typ][$key], ENT_QUOTES, "UTF-8"));
					}
					$list[$key] = $values;
				}
			}

			ReplacementStringUtil::saveConfig($list);
			$this->configObj->redirect("updated");
		}
	}

	function execute(){
    	parent::__construct();

    	if(isset($_GET["remove"])) self::_remove();

		$this->addForm("form");
		$list = ReplacementStringUtil::getConfig();

		DisplayPlugin::toggle("has_symbol_list", count($list));

		$this->addForm("change_form");

		SOY2::import("module.plugins.replacement_string.component.ThListComponent");
		$this->createAdd("lang_list", "ThListComponent", array(
			"list" => $this->langs,
		));

		SOY2::import("module.plugins.replacement_string.component.ReplacementStringListComponent");
		$this->createAdd("string_list", "ReplacementStringListComponent", array(
			"list" => $list,
			"languages" => $this->langs
		));
	}

	private function _remove(){
		$list = ReplacementStringUtil::getConfig();
		if(isset($list[$_GET["remove"]])){
			unset($list[$_GET["remove"]]);
			//要素を詰める
			$array = array();
			if(count($list)){
				foreach($list as $values){
					$array[] = $values;
				}
			}

    		ReplacementStringUtil::saveConfig($list);
    		$this->configObj->redirect("updated");
		}
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
