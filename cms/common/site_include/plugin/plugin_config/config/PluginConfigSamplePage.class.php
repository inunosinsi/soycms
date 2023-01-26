<?php

class PluginConfigSamplePage extends WebPage {

	private $pluginObj;

	function __construct(){

	}

	function doPost(){

	}

	function execute(){
		parent::__construct();
	}

	/**
	 * 当PHPファイルと対になるHTMLファイルを指定する
	 * この関数を省略すると、同階層の同名のHTMLファイルを読み込みます
	 */
	function getTemplateFilePath(){
		return dirname(__FILE__) . "/PluginConfigSamplePage.html";
	}

	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}