<?php
class CommonPointGrantConfigFormPage extends WebPage{

	private $configObj;

    function __construct() {
    	SOY2DAOFactory::importEntity("SOYShop_DataSets");
    	SOY2::imports("module.plugins.common_point_grant.util.*");
    }
    
    function doPost(){
    	
    	if(soy2_check_token() && isset($_POST["Config"])){
			PointGrantUtil::saveConfig($_POST["Config"]);			
			$this->configObj->redirect("updated");
			
		}
    }
    
    function execute(){
    	WebPage::__construct();
    	
    	$config = PointGrantUtil::getConfig();
    	
		DisplayPlugin::toggle("error", isset($_GET["error"]));

		$this->addForm("form");
		
		$this->addCheckBox("sale_point_double_on", array(
			"name" => "Config[sale_point_double_on]",
			"value" => 1,
			"selected" => (isset($config["sale_point_double_on"]) && (int)$config["sale_point_double_on"])
		));
		
		$this->addInput("sale_point_double", array(
			"name" => "Config[sale_point_double]",
			"value" => (isset($config["sale_point_double"])) ? (int)$config["sale_point_double"] : 1,
			"style" => "width:50px;text-align:right;"
		));
		
		$this->addInput("point_birthday_present", array(
			"name" => "Config[point_birthday_present]",
			"value" => (isset($config["point_birthday_present"])) ? (int)$config["point_birthday_present"] : 0,
			"style" => "width:100px;text-align:right;"
		));
    }
    
    function setConfigObj($obj) {
		$this->configObj = $obj;
	}
}
?>