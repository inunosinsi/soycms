<?php
class SOYListConnectorConfigPage extends WebPage{
	
	private $pluginObj;
	
	function SOYListConnectorConfigPage(){
		
	}
	
	function doPost(){

	}
		
	function execute(){				
		WebPage::WebPage();
	}
	
	function getTemplateFilePath(){
		return dirname(__FILE__)."/config.html";
	}

	function getPluginObj() {
		return $this->pluginObj;
	}
	function setPluginObj($pluginObj) {
		$this->pluginObj = $pluginObj;
	}
}
?>