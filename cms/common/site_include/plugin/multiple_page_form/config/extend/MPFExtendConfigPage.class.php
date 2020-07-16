<?php

class MPFExtendConfigPage extends WebPage {

	private $pluginObj;
	private $hash;

	function __construct(){
		SOY2::import("site_include.plugin.multiple_page_form.util.MPFTypeExtendUtil");
	}

	function doPost(){
		if(soy2_check_token()){
			$cnf = MultiplePageFormUtil::readJson($this->hash);

			$cnf["name"] = (isset($_POST["Config"]["name"])) ? $_POST["Config"]["name"] : "";
			$cnf["next"] = (isset($_POST["Config"]["next"])) ? $_POST["Config"]["next"] : "";
			$cnf["extend"] = (isset($_POST["Config"]["extend"])) ? $_POST["Config"]["extend"] : "";
			$cnf["description"] = (isset($_POST["Config"]["description"])) ? $_POST["Config"]["description"] : "";

			MultiplePageFormUtil::savePageConfig($this->hash, $cnf);

			CMSPlugin::redirectConfigPage();
		}
	}

	function execute(){
		parent::__construct();

		$this->addLabel("page_class_dir", array(
			"text" => MPFTypeExtendUtil::getPageDir()
		));

		MPFTypeExtendUtil::getPageClassList();

		self::_buildConfigForm();
	}

	private function _buildConfigForm(){
		$cnf = MultiplePageFormUtil::readJson($this->hash);

		$this->addForm("form");

		$this->addInput("page_name", array(
			"name" => "Config[name]",
			"value" => MultiplePageFormUtil::getPageName($this->hash),
			"attr:required" => "required"
		));

		$this->addLabel("page_type", array(
			"text" => MultiplePageFormUtil::getTypeText($cnf["type"])
		));

		$this->addTextArea("page_description", array(
			"name" => "Config[description]",
			"value" => (isset($cnf["description"])) ? $cnf["description"] : ""
		));

		$this->addSelect("extend", array(
			"name" => "Config[extend]",
			"options" => MPFTypeExtendUtil::getPageClassList(),
			"selected" => (isset($cnf["extend"])) ? $cnf["extend"] : ""
		));

		$this->addSelect("next_page_type", array(
			"name" => "Config[next]",
			"options" => MultiplePageFormUtil::getPageItemList($this->hash),
			"selected" => (isset($cnf["next"])) ? $cnf["next"] : ""
		));
	}

	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}

	function setHash($hash){
		$this->hash = $hash;
	}
}
