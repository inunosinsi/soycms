<?php
class SOYListConnectorConfigPage extends WebPage{
	
	private $pluginObj;
	
	function __construct(){
		
	}
	
	function doPost(){

	}
		
	function execute(){				
		parent::__construct();
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