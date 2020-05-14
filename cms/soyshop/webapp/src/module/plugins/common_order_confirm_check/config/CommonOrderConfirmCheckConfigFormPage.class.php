<?php

class CommonOrderConfirmCheckConfigFormPage extends WebPage{

    function __construct() {
    	SOY2DAOFactory::importEntity("SOYShop_DataSets");
    }

    function doPost(){

    	if(soy2_check_token() && isset($_POST["Config"])){
    		SOYShop_DataSets::put("common_order_confirm_check", $_POST["Config"]);
			SOY2PageController::jump("Config.Detail?plugin=common_order_confirm_check&updated");
    	}
    }

    function execute(){
    	include_once(dirname(dirname(__FILE__)) . "/class/common.php");

    	parent::__construct();

    	$config = CommonOrderConfirmCheckCommon::getConfig();

    	$this->addForm("form");

    	$this->addTextArea("text", array(
    		"name" => "Config[text]",
    		"value" => (isset($config["text"])) ? $config["text"] : ""
    	));

    	$this->addInput("error", array(
    		"name" => "Config[error]",
    		"value" => (isset($config["error"])) ? $config["error"] : "",
    		"style" => "width:90%;"
    	));

    }

    function setConfigObj($obj) {
		$this->config = $obj;
	}
}
?>
