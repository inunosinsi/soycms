<?php

class LimitationBrowseBlogEntryConfigFormPage extends WebPage{
	
	private $pluginObj;
	
	function __construct(){}
	
	function execute(){
		WebPage::__construct();
	}
	
	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}
?>