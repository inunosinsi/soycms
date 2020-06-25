<?php

class SOYInquiryConnectPage extends WebPage {

	private $pluginObj;

	function __construct(){
		SOY2::import("site_include.plugin.multiple_page_form.util.SOYInquiryConnectUtil");
	}

	function doPost(){
		if(soy2_check_token()){
			SOYInquiryConnectUtil::saveConfig($_POST["Config"]);
			CMSPlugin::redirectConfigPage();
		}
	}

	function execute(){
		parent::__construct();

		$cnf = SOYInquiryConnectUtil::getConfig();

		$this->addForm("form");

		$this->addSelect("form_id", array(
			"name" => "Config[form_id]",
			"options" => SOYInquiryConnectUtil::getInquiryFormList(),
			"selected" => (isset($cnf["form_id"])) ? $cnf["form_id"] : null
		));
	}

	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}
