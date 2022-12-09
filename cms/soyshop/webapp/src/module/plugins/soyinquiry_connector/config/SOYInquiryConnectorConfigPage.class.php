<?php

class SOYInquiryConnectorConfigPage extends WebPage {

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.soyinquiry_connector.util.SOYInquiryConnectorUtil");
	}

	function doPost(){
		if(soy2_check_token() && isset($_POST["Config"])){
			SOYInquiryConnectorUtil::saveConfig($_POST["Config"]);
			$this->config->redirect("updated");
		}
	}

	function execute(){
		parent::__construct();

		$this->addForm("form");

		$cnf = SOYInquiryConnectorUtil::getConfig();

		$this->addInput("url", array(
			"name" => "Config[url]",
			"value" => (isset($cnf["url"])) ? $cnf["url"] : ""
		));
	}

	function setConfigObj($configObj) {
		$this->configObj = $configObj;
	}

}
