<?php
class SOYCMS_ThisIsNew_Plugin_FormPage extends WebPage{

	private $pluginObj;

	function __construct(){}

	function doPost(){
		if(soy2_check_token()){
			if(isset($_POST["days_to_be_new"])){
				$this->pluginObj->daysToBeNew = $_POST["days_to_be_new"];
				$this->pluginObj->ignoreFutureEntry = $_POST["ignore_future_entry"];
				CMSPlugin::savePluginConfig($this->pluginObj->getId(),$this->pluginObj);
			}
			CMSPlugin::redirectConfigPage();
		}

	}

	function execute(){
		parent::__construct();

		$this->addLabel("cms_id", array(
			"text" => SOYCMS_ThisIsNew_Plugin::CMS_ID
		));


		$this->addForm("this_is_new_form");

		$this->addInput("days_to_be_new", array(
			"name" => "days_to_be_new",
			"value" => $this->pluginObj->daysToBeNew,
		));

		$this->addCheckBox("ignore_future_entry", array(
			"type" => "checkbox",
			"name" => "ignore_future_entry",
			"value" => 1,
			"selected" => $this->pluginObj->ignoreFutureEntry,
			"isBool" => true,
			"label" => "未来の記事は新着として扱わない"
		));
	}

	function setPluginObj($pluginObj) {
		$this->pluginObj = $pluginObj;
	}

	function getTemplateFilePath(){
		return dirname(__FILE__)."/config_form.html";
	}
}
