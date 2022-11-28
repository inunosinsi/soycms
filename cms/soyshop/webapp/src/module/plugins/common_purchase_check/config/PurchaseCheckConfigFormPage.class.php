<?php

class PurchaseCheckConfigFormPage extends WebPage{

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.common_purchase_check.util.PurchaseCheckUtil");
	}

	function doPost(){

		if(soy2_check_token()){

			$config = (isset($_POST["Config"])) ? $_POST["Config"] : array();
			PurchaseCheckUtil::saveConfig($config);

			$this->configObj->redirect("updated");
		}
	}

	function execute(){
		parent::__construct();

		$config = PurchaseCheckUtil::getConfig();

		$this->addForm("form");

		$this->addCheckBox("paid", array(
			"name" => "Config[paid]",
			"value" => 1,
			"selected" => (isset($config["paid"]) && $config["paid"] == 1),
			"label" => "入金済みまで行って購入済みとする"
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
?>
