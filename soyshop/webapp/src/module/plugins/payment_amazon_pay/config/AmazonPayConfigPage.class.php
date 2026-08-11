<?php

class AmazonPayConfigPage extends WebPage {

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.payment_amazon_pay.util.AmazonPayUtil");
	}

	function doPost(){
		if(soy2_check_token()){
			AmazonPayUtil::saveConfig($_POST["Config"]);
			$this->configObj->redirect("updated");
		}
	}

	function execute(){
		parent::__construct();

		$cnf = AmazonPayUtil::getConfig();

		$this->addForm("form");

		$this->addCheckBox("sandbox", array(
			"name" => "Config[sandbox]",
			"value" => 1,
			"selected" => (isset($cnf["sandbox"]) && $cnf["sandbox"] == 1),
			"label" => "テストモート"
		));

		foreach(array("test", "production") as $t){
			foreach(array("merchant_id", "access_key_id", "secret_access_key", "client_id", "client_secret") as $tt){
				$this->addInput($t . "_" . $tt, array(
					"name" => "Config[" . $t . "][" . $tt . "]",
					"value" => (isset($cnf[$t][$tt])) ? $cnf[$t][$tt] : ""
				));
			}
		}

		$this->addLabel("redirect_url", array(
			"text" => AmazonPayUtil::getRedirectUrl()
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
