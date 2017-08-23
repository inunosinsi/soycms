<?php

class CommonThisIsNewConfigFormPage extends WebPage{

    function __construct() {
    	SOY2DAOFactory::importEntity("SOYShop_DataSets");
    }
    
    function doPost(){
    	
    	if(soy2_check_token()&&isset($_POST["Config"])){
			$config = $_POST["Config"];
			$config["date"] = mb_convert_kana($config["date"], "a");
			$config["date"] = (is_numeric($config["date"]) && $config> 0)?(int)$config["date"] : 0;
			
			SOYShop_DataSets::put("common_this_is_new", $config);
			SOY2PageController::jump("Config.Detail?plugin=common_this_is_new&updated");
    	}
    }
    
    function execute(){
    	parent::__construct();
    	
    	$config = $this->getConfig();
    	
    	$this->createAdd("updated","HTMLModel", array(
    		"visible" => (isset($_GET["updated"]))
    	));
  	
    	$this->addForm("form");
    	
    	$this->createAdd("date","HTMLInput", array(
    		"name" => "Config[date]",
    		"value" => @$config["date"]
    	));
    }
    
    function getConfig(){
    	return SOYShop_DataSets::get("common_this_is_new", array(
    		"date" => 7
    	));
    }

    function setConfigObj($obj) {
		$this->config = $obj;
	}
}
?>