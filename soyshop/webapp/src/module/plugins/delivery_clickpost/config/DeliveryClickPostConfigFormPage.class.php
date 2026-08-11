<?php
class DeliveryClickPostConfigFormPage extends WebPage{

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.delivery_clickpost.util.ClickPostUtil");
	}

	function doPost(){
		if(soy2_check_token()){
			ClickPostUtil::saveConfig($_POST["Config"]);
			$this->configObj->redirect("updated");
		}
	}

	function execute(){
		parent::__construct();

		$cfg = ClickPostUtil::getConfig();

		$this->addForm("form");

		$this->addInput("name", array(
			"name" => "Config[name]",
			"value" => (isset($cfg["name"])) ? $cfg["name"] : ""
		));
		
		$this->addTextArea("description", array(
			"name" => "Config[description]",
			"value" => (isset($cfg["description"])) ? $cfg["description"] : ""
		));

		$this->addInput("fee_per_pack", array(
			"name" => "Config[fee]",
			"value" => (isset($cfg["fee"])) ? (int)$cfg["fee"] : 0,
			"style" => "width:60px;"
		));

		$this->addInput("shipping_free", array(
			"name" => "Config[shippingFree]",
			"value" => (isset($cfg["shippingFree"]) && is_numeric($cfg["shippingFree"])) ? (int)$cfg["shippingFree"] : "",
			"style" => "width:100px;"
		));

		SOY2::import("module.plugins.delivery_clickpost.component.FeePerPackConfigListComponent");
		$this->createAdd("fee_per_pack_config", "FeePerPackConfigListComponent", array(
			"list" => (isset($cfg["feePerPack"]) && is_array($cfg["feePerPack"])) ? $cfg["feePerPack"] : array(),
		));
	}

	function setConfigObj(DeliveryClickPostConfig $configObj) {
		$this->configObj = $configObj;
	}
}
