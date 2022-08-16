<?php

class CustomSearchFieldEntryListConfigPage extends WebPage {

	private $pluginObj;

	function __construct(){

	}

	function execute(){
		parent::__construct();
		DisplayPlugin::toggle("noinstalled_customSearchField", !CMSPlugin::activeCheck("CustomSearchField"));
	}

	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}
