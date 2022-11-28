<?php

class ReRegiserConfigPage extends WebPage {

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.order_re_register.util.ReRegiserUtil");
	}

	function doPost(){
		if(soy2_check_token()){
			$config = (isset($_POST["Config"])) ? $_POST["Config"] : array();
			ReRegiserUtil::saveConfig($config);
			$this->configObj->redirect("updated");
		}
	}

	function execute(){
		parent::__construct();

		$config = ReRegiserUtil::getConfig();

		$this->addForm("form");

		$this->addCheckBox("customer", array(
			"name" => "Config[customer]",
			"value" => 1,
			"selected" => (isset($config["customer"]) && $config["customer"] == 1),
			"label" => "注文再登録時に顧客情報も自動入力する"
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
