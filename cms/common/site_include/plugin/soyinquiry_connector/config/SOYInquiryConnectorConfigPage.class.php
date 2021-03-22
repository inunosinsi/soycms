<?php

class SOYInquiryConnectorConfigPage extends WebPage {

	private $pluginObj;

	function __construct(){
		SOY2::import("site_include.plugin.soyinquiry_connector.util.SOYInquiryConnectorUtil");
	}

	function doPost(){
		if(soy2_check_token() && isset($_POST["Config"])){
			$this->pluginObj->setPageId((int)$_POST["Config"]["pageId"]);
			CMSPlugin::savePluginConfig($this->pluginObj->getId(), $this->pluginObj);
			CMSPlugin::redirectConfigPage();
		}
	}

	function execute(){
		parent::__construct();

		$this->addForm("form");

		$this->addSelect("pages", array(
			"name" => "Config[pageId]",
			"options" => SOYInquiryConnectorUtil::getInquiryPageList(true),
			"selected" => $this->pluginObj->getPageId()
		));
	}

	function setPluginObj($pluginObj) {
		$this->pluginObj = $pluginObj;
	}
}
