<?php

class CommonItemOptionExImportPage extends WebPage{
	
	private $configObj;
	
	function CommonItemOptionExImportPage(){
		
	}
	
	function doPost(){
		if(soy2_check_token() && isset($_POST["import"]) && strlen(trim($_POST["configure"])) > 0){
			$value = trim($_POST["configure"]);
			$value = base64_decode($value);
			
			$configs = soy2_unserialize($value);
			if(is_array($configs)){
				SOYShop_DataSets::put("item_option", soy2_serialize($configs));
			}else{
				SOY2PageController::jump("Config.Detail?plugin=common_item_option&import&failed");
			}
			
			SOY2PageController::jump("Config.Detail?plugin=common_item_option&updated");
			exit;
		}
	}
	
	function execute(){
		$configs = SOYShop_DataSets::get("item_option", null);
		if(is_null($configs)) SOY2PageController::jump("Config.Detail?plugin=common_item_option");
		
		WebPage::WebPage();
		
		$this->addForm("form");		
		$value = base64_encode(soy2_serialize($configs));		
		
		$this->addTextArea("export_value", array(
			"value" => $value,
			"style" => "height:200px;",
			"onclick" => "this.select();"
		));
		
		$this->addTextArea("import_value", array(
			"name" => "configure",
			"style" => "height:200px;"
		));
	}
	
	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}