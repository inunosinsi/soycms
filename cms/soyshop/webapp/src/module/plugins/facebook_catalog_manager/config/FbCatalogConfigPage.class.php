<?php

class FbCatalogConfigPage extends WebPage {

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.facebook_catalog_manager.util.FbCatalogManagerUtil");
	}

	function doPost(){
		if(soy2_check_token() && isset($_POST["Config"])){
			FbCatalogManagerUtil::saveConfig($_POST["Config"]);
			$this->configObj->redirect("updated");
		}
	}

	function execute(){
		parent::__construct();

		$cnf = FbCatalogManagerUtil::getConfig();

		$this->addForm("form");

		$this->addInput("shop_name", array(
			"name" => "Config[shopName]",
			"value" => $cnf["shopName"]
		));

		$this->addTextArea("shop_description", array(
			"name" => "Config[shopDescription]",
			"value" => $cnf["shopDescription"]
		));

		$this->addInput("brand", array(
			"name" => "Config[brand]",
			"value" => (isset($cnf["brand"])) ? $cnf["brand"] : ""
		));

		$this->addInput("shipping_price", array(
			"name" => "Config[shippingPrice]",
			"value" => (isset($cnf["shippingPrice"]) && is_numeric($cnf["shippingPrice"])) ? $cnf["shippingPrice"] : 0,
			"style" => "width:80px;"
		));

		$this->addLabel("xml_url", array(
			"text" => soyshop_get_page_url("facebook_catalog_manager.xml")
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
