<?php

class LimitationBrowseBlogEntryConfigFormPage extends WebPage{
	
	private $pluginObj;
	
	function __construct(){}
	
	function execute(){
		WebPage::WebPage();
	}
	
	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}
?>