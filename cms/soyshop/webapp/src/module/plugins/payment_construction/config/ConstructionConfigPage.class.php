<?php

class ConstructionConfigPage extends WebPage{

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.payment_construction.util.PaymentConstructionUtil");
	}

	function doPost(){
		if(soy2_check_token()){
			PaymentConstructionUtil::saveConfig($_POST["Config"]);
			$this->configObj->redirect("updated");
		}
	}

	function execute(){
		parent::__construct();
		$config = PaymentConstructionUtil::getConfig();

		$this->addForm("form");

		$this->addTextArea("items", array(
			"name" => "Config[items]",
			"value" => (isset($config["items"])) ? $config["items"] : null
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
