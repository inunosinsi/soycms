<?php

class PingConfigPage extends WebPage {

	private $pluginObj;

	function __construct(){}

	function doPost(){
		if(soy2_check_token()){
			$this->pluginObj->setPingServers(trim($_POST["pingServers"]));

			CMSPlugin::savePluginConfig(PingNewPlugin::PLUGIN_ID, $this->pluginObj);
			CMSPlugin::redirectConfigPage();
		}
	}

	function execute(){
		parent::__construct();

		$this->addForm("form");

		$this->addTextArea("ping_servers", array(
			"name" => "pingServers",
			"value" => $this->pluginObj->getPingServers()
		));
	}

	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}
