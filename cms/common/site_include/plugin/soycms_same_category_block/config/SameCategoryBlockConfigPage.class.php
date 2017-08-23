<?php
class SameCategoryBlockConfigPage extends WebPage{

	private $pluginObj;

	function __construct(){}

	function doPost(){}

	function execute(){
		parent::__construct();
	}

	function getPluginObj() {
		return $this->pluginObj;
	}
	function setPluginObj($pluginObj) {
		$this->pluginObj = $pluginObj;
	}
}
