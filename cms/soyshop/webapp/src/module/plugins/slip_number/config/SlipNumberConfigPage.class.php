<?php

class SlipNumberConfigPage extends WebPage{
	
	private $pluginObj;
	
	function __construct(){
		SOY2::import("module.plugins.slip_number.util.SlipNumberUtil");
	}
	
	function doPost(){
		if(soy2_check_token()){
			SlipNumberUtil::saveConfig($_POST["Config"]);
		}
		$this->pluginObj->redirect("updated");
	}
	
	function execute(){
		parent::__construct();
		
		$config = SlipNumberUtil::getConfig();
		
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