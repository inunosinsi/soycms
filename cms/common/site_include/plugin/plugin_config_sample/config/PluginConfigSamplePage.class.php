<?php

class PluginConfigSamplePage extends WebPage {

	private $pluginObj;

	function __construct(){

	}

	function doPost(){
		if(soy2_check_token()){
			// 設定に関する処理を追加
		}

		// 同じページにリダイレクト
		CMSPlugin::redirectConfigPage();
	}

	function execute(){
		parent::__construct();

		$this->createAdd("form", "HTMLForm");

		$this->createAdd("number", "HTMLInput", array(
			"name" => "n",
			"value" => 0
		));
	}

	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}