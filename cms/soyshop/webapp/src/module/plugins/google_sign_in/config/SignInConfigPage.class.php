<?php

class SignInConfigPage extends WebPage {

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.google_sign_in.util.GoogleSignInUtil");
	}

	function doPost(){
		if(soy2_check_token()){
			GoogleSignInUtil::saveConfig($_POST["Config"]);
			$this->configObj->redirect("updated");
		}
	}

	function execute(){
		parent::__construct();

		$config = GoogleSignInUtil::getConfig();

		$this->addForm("form");

		$this->addInput("client_id", array(
			"name" => "Config[client_id]",
			"value" => (isset($config["client_id"])) ? $config["client_id"] : ""
		));

		$this->addLabel("create_js_url_sample", array(
			"text" => str_replace("/" . SOYSHOP_ID . "/", "", soyshop_get_site_url(true))
		));

		//説明用
		$this->addLabel("redirect_url_sample", array(
			"text" => soyshop_get_mypage_url(true)
		));

		$this->addLink("mypage_login_link", array(
			"text" => soyshop_get_mypage_url(true) . "/login",
			"link" => soyshop_get_mypage_url(true) . "/login"
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
