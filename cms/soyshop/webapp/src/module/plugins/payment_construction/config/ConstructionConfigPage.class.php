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

		$this->addCheckBox("display_construction_item", array(
			"name" => "Config[display_construction_item]",
			"value" => 1,
			"selected" => (!isset($config["display_construction_item"]) || (int)$config["display_construction_item"] === 1),
			"label" => "注文画面に施工費の項目を表示する"
		));

		$this->addTextArea("items", array(
			"name" => "Config[items]",
			"value" => (isset($config["items"])) ? $config["items"] : null
		));

		$this->addTextArea("items_include", array(
			"name" => "Config[items_include]",
			"value" => (isset($config["items_include"])) ? $config["items_include"] : null
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
