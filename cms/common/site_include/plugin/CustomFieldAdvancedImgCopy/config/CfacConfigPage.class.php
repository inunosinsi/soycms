<?php

class CfacConfigPage extends WebPage {

	private $pluginObj;

	function __construct(){}

	function doPost(){
		if(soy2_check_token()){
			$this->pluginObj->setPostfix($_POST["Config"]["postfix"]);
			CMSPlugin::savePluginConfig($this->pluginObj->getId(),$this->pluginObj);
			CMSPlugin::redirectConfigPage();
		}
	}

	function execute(){
		parent::__construct();

		$this->addForm("form");

		$this->addInput("postfix", array(
			"name" => "Config[postfix]",
			"value" => $this->pluginObj->getPostfix()
		));
	}

	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}
