<?php

class AutoSaveEntryConfigFormPage extends WebPage{
	
	private $pluginObj;
	
	function __construct(){}
	
	function doPost(){
		if(soy2_check_token() && isset($_POST["Config"])){
			
			if(isset($_POST["Config"]["period"]) && (int)$_POST["Config"]["period"] > 0){
				$this->pluginObj->setPeriod($_POST["Config"]["period"]);
			}
			
			CMSPlugin::savePluginConfig($this->pluginObj->getId(), $this->pluginObj);
			CMSPlugin::redirectConfigPage();
		}
	}
	
	function execute(){
		parent::__construct();
		
		$this->addForm("form");
		
		$this->addInput("save_period", array(
			"name" => "Config[period]",
			"value" => (int)$this->pluginObj->getPeriod(),
			"style" => "width:80px; text-align:rignt;"
		));
	}
	
	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}
?>