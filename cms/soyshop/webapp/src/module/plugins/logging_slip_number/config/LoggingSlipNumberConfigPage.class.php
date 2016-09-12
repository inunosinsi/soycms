<?php

class LoggingSlipNumberConfigPage extends WebPage{
	
	private $pluginObj;
	
	function __construct(){
		SOY2::import("module.plugins.logging_slip_number.util.LoggingSlipNumberUtil");
	}
	
	function doPost(){
		if(soy2_check_token()){
			LoggingSlipNumberUtil::saveConfig($_POST["Config"]);
		}
		$this->pluginObj->redirect("updated");
	}
	
	function execute(){
		WebPage::__construct();
		
		$config = LoggingSlipNumberUtil::getConfig();
		
		$this->addForm("form");
		
		$this->addTextArea("content", array(
			"name" => "Config[content]",
			"value" => (isset($config["content"])) ? $config["content"] : ""
		));
	}
	
	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}