<?php

class CustomReplaceConfigPage extends WebPage {

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.customfield_replacement_string.util.CustomReplaceUtil");
	}

	function doPost(){
		if(soy2_check_token() && isset($_POST["Config"])){
			CustomReplaceUtil::saveConfig($_POST["Config"]);
			$this->configObj->redirect("updated");
		}
		$this->configObj->redirect("failed");
	}

	function execute(){
		parent::__construct();

		DisplayPlugin::toggle("failed", isset($_GET["failed"]));

		$cnf = CustomReplaceUtil::getConfig();

		$this->addForm("form");

		$this->addSelect("field_id", array(
			"name" => "Config[fieldId]",
			"options" => self::_getCustomfieldList(),
			"selected" => (isset($cnf["fieldId"]) && strlen($cnf["fieldId"])) ? $cnf["fieldId"] : false
		));

		$this->addInput("format", array(
			"name" => "Config[format]",
			"value" => (isset($cnf["format"])) ? $cnf["format"] : ""
		));

		$this->addLabel("replacement", array(
			"html" => self::_replacementStringList()
		));
	}

	private function _getCustomfieldList(){
		SOY2::import("domain.shop.SOYShop_ItemAttribute");
		$cnfs = SOYShop_ItemAttributeConfig::load(true);
		if(!count($cnfs)) return array();

		$list = array();
		foreach($cnfs as $fieldId => $cnf){
			$list[$fieldId] = $cnf->getLabel() . "(" . $fieldId . ")";
		}
		return $list;
	}

	private function _replacementStringList(){
		$replaces = CustomReplaceUtil::getReplacementStringList();
		$html = array();
		$html[] = "<ul>";
		foreach($replaces as $rpl => $label){
			$html[] = "<li><strong>##" . $rpl . "##</strong>：" . $label . "</li>";
		}
		$html[] = "</ul>";
		return implode("\n", $html);
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
