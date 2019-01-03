<?php

class CustomSearchFieldEntryListConfigPage extends WebPage {

	private $pluginObj;

	function __construct(){

	}

	function execute(){
		parent::__construct();
		DisplayPlugin::toggle("noinstalled_customSearchField", !file_exists(UserInfoUtil::getSiteDirectory(true) . ".plugin/CustomSearchField.active"));
	}

	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}
