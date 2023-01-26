<?php

class PluginConfigSamplePage extends WebPage {

	private $pluginObj;

	function __construct(){
		SOY2::import("domain.cms.DataSets");
	}

	function doPost(){
		if(soy2_check_token()){
			DataSets::put("plugin_config_key", $_POST["n"]);
		}

		// 同じページにリダイレクト
		CMSPlugin::redirectConfigPage();
	}

	function execute(){
		parent::__construct();

		$this->createAdd("form", "HTMLForm");

		$this->createAdd("number", "HTMLInput", array(
			"name" => "n",
			"value" => DataSets::get("plugin_config_key", 0)
		));
	}

	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}