<?php

class LINELoginConfigPage extends WebPage{

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.line_login.util.LINELoginUtil");
	}

	function doPost(){
		if(soy2_check_token()){
			LINELoginUtil::saveConfig($_POST["Config"]);
			$this->configObj->redirect("updated");
		}
	}

	function execute(){
		parent::__construct();

		$config = LINELoginUtil::getConfig();

		$this->addForm("form");

		$this->addInput("channel_id", array(
			"name" => "Config[channel_id]",
			"value" => (isset($config["channel_id"])) ? $config["channel_id"] : ""
		));

		$this->addInput("channel_secret", array(
			"name" => "Config[channel_secret]",
			"value" => (isset($config["channel_secret"])) ? $config["channel_secret"] : ""
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
