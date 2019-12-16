<?php

class reCAPTCHAv3ConfigPage extends WebPage {

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.reCAPTCHAv3.util.reCAPTCHAUtil");
	}

	function doPost(){
		if(soy2_check_token()){
			reCAPTCHAUtil::saveConfig($_POST["Config"]);
			$this->configObj->redirect("updated");
		}
	}

	function execute(){
		parent::__construct();

		$config = reCAPTCHAUtil::getConfig();

		$this->addForm("form");

		$this->addInput("site_key", array(
			"name" => "Config[site_key]",
			"value" => (isset($config["site_key"])) ? $config["site_key"] : ""
		));

		$this->addInput("secret_key", array(
			"name" => "Config[secret_key]",
			"value" => (isset($config["secret_key"])) ? $config["secret_key"] : ""
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
