<?php

class LimitationBrowseBlogEntryConfigFormPage extends WebPage{
	
	private $pluginObj;
	
	function LimitationBrowseBlogEntryConfigFormPage(){
		
	}
	
	function execute(){
		WebPage::WebPage();
	}
	
	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}
?>