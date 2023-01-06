<?php

class ConvertFileNameConfigPage extends WebPage{

	private $pluginObj;

	function __construct(){}

	function doPost(){
		if(soy2_check_token()){
			$this->pluginObj->setLen((int)$_POST["Config"]["len"]);
			CMSPlugin::savePluginConfig($this->pluginObj->getId(),$this->pluginObj);
			CMSPlugin::redirectConfigPage();
		}
	}

	function execute(){
		parent::__construct();

		$this->addForm("form");

		$this->addInput("len", array(
			"name" => "Config[len]",
			"value" => $this->pluginObj->getLen(),
			"style" => "width:100px;"
		));
	}

	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}
