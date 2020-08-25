<?php

class LoginWithAmazonConfigPage extends WebPage {

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.login_with_amazon.util.LoginWithAmazonUtil");
	}

	function doPost(){
		if(soy2_check_token()){
			LoginWithAmazonUtil::saveConfig($_POST["Config"]);
			$this->configObj->redirect("updated");
		}
	}

	function execute(){
		parent::__construct();

		$cnf = LoginWithAmazonUtil::getConfig();

		$this->addForm("form");

		foreach(array("client_id", "client_secret") as $t){
			$this->addInput($t, array(
				"name" => "Config["  . $t . "]",
				"value" => (isset($cnf[$t])) ? $cnf[$t]: ""
			));
		}

		$this->addLabel("allow_url", array(
			"text" => "https://" . $_SERVER["HTTP_HOST"]
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
