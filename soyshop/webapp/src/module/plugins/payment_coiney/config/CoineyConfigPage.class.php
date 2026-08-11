<?php

class CoineyConfigPage extends WebPage {

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.payment_coiney.util.CoineyUtil");
	}

	function doPost(){
		if(soy2_check_token()){
			CoineyUtil::saveConfig($_POST["Config"]);
			$this->configObj->redirect("updated");
		}
	}

	function execute(){
		parent::__construct();

		$config = CoineyUtil::getConfig();

		$this->addForm("form");

		$this->addCheckBox("sandbox", array(
			"name" => "Config[sandbox]",
			"value" => 1,
			"selected" => (isset($config["sandbox"]) && $config["sandbox"] == 1),
			"label" => "テストモート"
		));

		$this->addInput("key", array(
			"name" => "Config[key]",
			"value" => (isset($config["key"])) ? $config["key"] : ""
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
