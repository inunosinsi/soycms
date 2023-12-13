<?php

class BootstrapAssistantConfigPage extends WebPage {

	private $pluginObj;

	function __construct(){
	}

	function doPost(){
	}

	function execute(){
		parent::__construct();
	}

	function setPluginObj($pluginObj) {
		$this->pluginObj = $pluginObj;
	}
}
