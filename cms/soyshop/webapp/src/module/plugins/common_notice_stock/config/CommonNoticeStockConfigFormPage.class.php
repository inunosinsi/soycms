<?php

class CommonNoticeStockConfigFormPage extends WebPage{

    function __construct() {
    	SOY2DAOFactory::importEntity("SOYShop_DataSets");
    	SOY2::import("module.plugins.common_notice_stock.common.CommonNoticeStockCommon");
    }

    function doPost(){

    	if(soy2_check_token() && isset($_POST["Config"])){
    		$config = $_POST["Config"];
    		$config["stock"] = soyshop_convert_number($config["stock"], 10);

    		SOYShop_DataSets::put("notice_stock", $config);
			SOY2PageController::jump("Config.Detail?plugin=common_notice_stock&updated");
    	}
    }

    function execute(){
    	parent::__construct();

    	$config = CommonNoticeStockCommon::getConfig();

    	$this->addForm("form");

    	$this->addInput("stock", array(
    		"name" => "Config[stock]",
    		"value" => (isset($config["stock"])) ? (int)$config["stock"] : ""
    	));

    	$this->addTextArea("has_stock_text", array(
    		"name" => "Config[has_stock]",
    		"value" => (isset($config["has_stock"])) ? $config["has_stock"] : ""
    	));

    	$this->addTextArea("notice_stock_text", array(
    		"name" => "Config[notice_stock]",
    		"value" => (isset($config["notice_stock"])) ? $config["notice_stock"] : ""
    	));
    	$this->addTextArea("no_stock_text", array(
    		"name" => "Config[no_stock]",
    		"value" => (isset($config["no_stock"])) ? $config["no_stock"] : ""
    	));
    }

    function setConfigObj($obj) {
		$this->config = $obj;
	}
}
