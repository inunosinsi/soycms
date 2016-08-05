<?php

class CommonSoymailConfigFormPage extends WebPage{

    function __construct() {
    	SOY2DAOFactory::importEntity("SOYShop_DataSets");
    }
    
    function doPost(){

		SOY2PageController::jump("Config.Detail?plugin=common_soymail&updated");
    }
    
    function execute(){
    	WebPage::WebPage();
    }

    function setConfigObj($obj) {
		$this->config = $obj;
	}
}
?>