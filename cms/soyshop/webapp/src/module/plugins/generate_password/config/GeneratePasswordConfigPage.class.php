<?php

class GeneratePasswordConfigPage extends WebPage{

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.generate_password.util.GeneratePasswordUtil");
	}

	function doPost(){
		if(soy2_check_token()){
			GeneratePasswordUtil::saveConfig($_POST["Config"]);
			$this->configObj->redirect("updated");
		}
    }

	function execute(){
		parent::__construct();

		$cnf = GeneratePasswordUtil::getConfig();

		$this->addForm("form");

		$this->addInput("password_strlen", array(
			"name" => "Config[password_strlen]",
			"value" => (isset($cnf["password_strlen"])) ? (int)$cnf["password_strlen"] : 12,
			"style" => "width:80px;"
		));

		$this->addTextArea("insert_mail_text", array(
			"name" => "Config[insert_mail_text]",
			"value" => (isset($cnf["insert_mail_text"])) ? $cnf["insert_mail_text"] : "",
			"style" => "height:120px;"
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
