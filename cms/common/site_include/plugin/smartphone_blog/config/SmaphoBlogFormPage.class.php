<?php

class SmaphoBlogFormPage extends WebPage {

	private $pluginObj;

	function __construct(){}

	function doPost(){
		if(soy2_check_token()){
			$maxWidth = (isset($_POST["maxWidth"]) && (int)$_POST["maxWidth"] > 0) ? (int)$_POST["maxWidth"] : 640;
			$this->pluginObj->setMaxWidth($maxWidth);

			CMSUtil::notifyUpdate();
			CMSPlugin::savePluginConfig(SmartphoneBlogPlugin::PLUGIN_ID, $this->pluginObj);
			CMSPlugin::redirectConfigPage();
		}
	}

	function execute(){
		parent::__construct();

		$this->addForm("form");

		$this->addInput("max_width", array(
			"name" => "maxWidth",
			"value" => $this->pluginObj->getMaxWidth()
		));
	}

	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}
