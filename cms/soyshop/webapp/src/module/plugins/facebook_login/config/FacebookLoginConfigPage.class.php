<?php

class FacebookLoginConfigPage extends WebPage {

	private $cnfObj;

	function __construct(){
		SOY2::import("module.plugins.facebook_login.util.FacebookLoginUtil");
	}

	function doPost(){
		if(soy2_check_token()){
			FacebookLoginUtil::saveConfig($_POST["Config"]);
			$this->configObj->redirect("updated");
		}
	}

	function execute(){
		parent::__construct();

		$cnf = FacebookLoginUtil::getConfig();

		$this->addForm("form");

		$this->addInput("api_version", array(
			"name" => "Config[api_version]",
			"value" => (isset($cnf["api_version"])) ? $cnf["api_version"]: ""
		));

		$this->addInput("app_id", array(
			"name" => "Config[app_id]",
			"value" => (isset($cnf["app_id"])) ? $cnf["app_id"] : ""
		));

		$this->addInput("app_secret", array(
			"name" => "Config[app_secret]",
			"value" => (isset($cnf["app_secret"])) ? $cnf["app_secret"] : ""
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

	function setConfigObj($cnfObj){
		$this->configObj = $cnfObj;
	}
}
