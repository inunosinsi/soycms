<?php

class PayJpRecurringConfigPage extends WebPage {

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.payment_pay_jp_recurring.util.PayJpRecurringUtil");
	}

	function doPost(){
		if(soy2_check_token()){
			PayJpRecurringUtil::saveConfig($_POST["Config"]);
			$this->configObj->redirect("updated");
		}
	}

	function execute(){
		parent::__construct();

		$config = PayJpRecurringUtil::getConfig();

		$this->addForm("form");

		$this->addCheckBox("sandbox", array(
			"name" => "Config[sandbox]",
			"value" => 1,
			"selected" => (isset($config["sandbox"]) && $config["sandbox"] == 1),
			"label" => "テストモート"
		));

		foreach(array("test", "public") as $t){
			$this->addInput($t . "_secret_key", array(
				"name" => "Config[" . $t . "][key]",
				"value" => (isset($config[$t]["key"])) ? $config[$t]["key"] : ""
			));
		}
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
