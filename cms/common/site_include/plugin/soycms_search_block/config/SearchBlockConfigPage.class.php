<?php
class SearchBlockConfigPage extends WebPage{
	
	private $pluginObj;
	
	function SearchBlockConfigPage(){
		
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
