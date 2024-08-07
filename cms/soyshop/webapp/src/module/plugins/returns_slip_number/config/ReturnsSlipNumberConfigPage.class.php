<?php

class ReturnsSlipNumberConfigPage extends WebPage{

	private $pluginObj;

	function __construct(){
		SOY2::import("module.plugins.returns_slip_number.util.ReturnsSlipNumberUtil");
	}

	function doPost(){
		if(soy2_check_token()){
			ReturnsSlipNumberUtil::saveConfig($_POST["Config"]);
		}
		$this->pluginObj->redirect("updated");
	}

	function execute(){
		parent::__construct();

		$config = ReturnsSlipNumberUtil::getConfig();

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
