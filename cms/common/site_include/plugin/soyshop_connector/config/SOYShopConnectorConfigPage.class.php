<?php

class SOYShopConnectorConfigPage extends WebPage{

	private $pluginObj;
	private $configLogic;

	function __construct(){
		$this->configLogic = SOY2Logic::createInstance("site_include.plugin.soyshop_connector.logic.ConfigLogic");
	}

	function doPost(){

		if(soy2_check_token()){

			$this->pluginObj->setSiteId($_POST["Config"]["siteId"]);

			CMSPlugin::savePluginConfig($this->pluginObj->getId(),$this->pluginObj);
			CMSPlugin::redirectConfigPage();
		}
	}

	function execute(){
		parent::__construct();

		$this->addForm("form");

		$this->addSelect("shop_list", array(
			"name" => "Config[siteId]",
			"options" => $this->configLogic->getList(),
			"selected" => $this->pluginObj->getSiteId()
		));
	}

	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}
