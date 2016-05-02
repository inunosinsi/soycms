<?php
class RandomBlockConfigPage extends WebPage{
	
	private $pluginObj;
	
	function RandomBlockConfigPage(){
		
	}
	
	function doPost(){}
	
	function execute(){
		WebPage::WebPage();		
	}
	
	function getPluginObj() {
		return $this->pluginObj;
	}
	function setPluginObj($pluginObj) {
		$this->pluginObj = $pluginObj;
	}
}
