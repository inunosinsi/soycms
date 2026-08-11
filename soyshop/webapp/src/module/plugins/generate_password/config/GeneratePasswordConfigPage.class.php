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

		$this->addCheckBox("include_symbol", array(
			"name" => "Config[include_symbol]",
			"value" => 1,
			"selected" => (isset($cnf["include_symbol"]) && $cnf["include_symbol"] == 1),
			"label" => "パスワードの文字列に記号(" . soyshop_get_symbols() . ")を含める"
		));

		$this->addCheckBox("generate_pw_on_admin", array(
			"name" => "Config[generate_pw_on_admin]",
			"value" => 1,
			"selected" => (isset($cnf["generate_pw_on_admin"]) && $cnf["generate_pw_on_admin"] == 1),
			"label" => "管理画面で追加したアカウントもパスワードの自動生成の対象とする"
		));

		$this->addCheckBox("send_mail_on_admin", array(
			"name" => "Config[send_mail_on_admin]",
			"value" => 1,
			"selected" => (isset($cnf["send_mail_on_admin"]) && $cnf["send_mail_on_admin"] == 1),
			"label" => "管理画面でパスワードを自動生成した後、ユーザに対してパスワードの通知を行う"
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
