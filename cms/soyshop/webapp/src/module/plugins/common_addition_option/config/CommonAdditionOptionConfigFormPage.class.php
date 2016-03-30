<?php
include(dirname(dirname(__FILE__)) . "/common/common.php");
class CommonAdditionOptionConfigFormPage extends WebPage{

    function CommonAdditionOptionConfigFormPage() {
    	SOY2DAOFactory::importEntity("SOYShop_DataSets");
    }
    
    function doPost(){

		if(soy2_check_token() && isset($_POST["Config"])){
			$config = $_POST["Config"];
			$config["price"] = soyshop_convert_number($config["price"], 0);
			
			SOYShop_DataSets::put("addition_option", $config);
			SOY2PageController::jump("Config.Detail?plugin=common_addition_option&updated");
		}

    }
    
    function execute(){
    	
    	$config = CommonAdditionCommon::getConfig();
    	
    	WebPage::WebPage();
    	
    	$this->addForm("form");
    	
    	$this->addModel("updated", array(
			"visible" => (isset($_GET["updated"]))
    	));
    	
    	$this->addInput("addition_name", array(
    		"name" => "Config[name]",
    		"value" => (isset($config["name"])) ? $config["name"] : ""
    	));
    	
    	$this->addInput("addition_price", array(
    		"name" => "Config[price]",
    		"value" => (isset($config["price"])) ? (int)$config["price"] : 0
    	));
    	
    	$this->addTextArea("addition_text", array(
    		"name" => "Config[text]",
    		"value" => (isset($config["text"])) ? $config["text"] : ""
    	));
     }

    function setConfigObj($obj) {
		$this->config = $obj;
	}
}
?>