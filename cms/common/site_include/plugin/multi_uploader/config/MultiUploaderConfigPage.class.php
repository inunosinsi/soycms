<?php

class MultiUploaderConfigPage extends WebPage {

	private $pluginObj;

	function __construct(){}

	function execute(){
		parent::__construct();

		$this->addLabel("site_id", array(
			"text" => UserInfoUtil::getSite()->getSiteId()
		));
	}

	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}
