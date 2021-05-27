<?php

class SOYInquiryConnectorConfigPage extends WebPage {

	private $pluginObj;

	function __construct(){
		SOY2::import("site_include.plugin.soyinquiry_connector.util.SOYInquiryConnectorUtil");
	}

	function doPost(){
		if(soy2_check_token() && isset($_POST["Config"])){
			$cnf = $_POST["Config"];

			$siteId = (isset($cnf["siteId"]) && is_numeric($cnf["siteId"])) ? (int)$cnf["siteId"] : null;
			$pageId = (isset($cnf["pageId"]) && is_numeric($cnf["pageId"])) ? (int)$cnf["pageId"] : null;

			if($this->pluginObj->getSiteId() != $siteId) $pageId = null;

			$this->pluginObj->setSiteId($siteId);
			$this->pluginObj->setPageId($pageId);

			CMSPlugin::savePluginConfig($this->pluginObj->getId(), $this->pluginObj);
			CMSPlugin::redirectConfigPage();
		}
	}

	function execute(){
		parent::__construct();

		$this->addForm("form");

		$this->addSelect("site", array(
			"name" => "Config[siteId]",
			"options" => SOYInquiryConnectorUtil::getSiteList(),
			"selected" => $this->pluginObj->getSiteId()
		));

		$selectedSiteId = (is_numeric($this->pluginObj->getSiteId()));

		DisplayPlugin::toggle("pages_selectbox", $selectedSiteId);
		$this->addSelect("pages", array(
			"name" => "Config[pageId]",
			"options" => ($selectedSiteId) ? SOYInquiryConnectorUtil::getInquiryPageList($this->pluginObj->getSiteId(), true) : array(),
			"selected" => $this->pluginObj->getPageId()
		));
	}

	function setPluginObj($pluginObj) {
		$this->pluginObj = $pluginObj;
	}
}
