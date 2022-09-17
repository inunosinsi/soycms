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

		foreach(array("test", "production") as $t){
			foreach(array("secret", "public") as $tt){
				$this->addInput($t . "_" . $tt . "_key", array(
					"name" => "Config[" . $t . "][" . $tt . "_key]",
					"value" => (isset($config[$t][$tt . "_key"])) ? $config[$t][$tt . "_key"] : ""
				));
			}
		}

		$this->addLabel("base_html_path", array(
			"html" => dirname(dirname(__FILE__)) . "/option/<strong>PayJpRecurringOptionPage.html</strong>"
		));

		$this->addLabel("change_html_path", array(
			"html" => dirname(dirname(__FILE__)) . "/option/<strong>_PayJpRecurringOptionPage.html</strong>"
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
