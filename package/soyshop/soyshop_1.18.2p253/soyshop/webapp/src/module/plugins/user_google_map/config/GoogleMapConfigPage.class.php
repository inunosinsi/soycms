<?php

class GoogleMapConfigPage extends WebPage {

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.user_google_map.util.UserGoogleMapUtil");
	}

	function doPost(){
		if(soy2_check_token()){
			UserGoogleMapUtil::saveConfig($_POST["Config"]);
			$this->configObj->redirect("updated");
		}
	}

	function execute(){
		parent::__construct();

		$config = UserGoogleMapUtil::getConfig();

		$this->addForm("form");

		$this->addInput("google_maps_api_key", array(
			"name" => "Config[google_maps_api_key]",
			"value" => (isset($config["google_maps_api_key"])) ? $config["google_maps_api_key"] : ""
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
