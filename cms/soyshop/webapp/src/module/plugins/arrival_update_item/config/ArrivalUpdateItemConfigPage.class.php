<?php

class ArrivalUpdateItemConfigPage extends WebPage{

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.arrival_update_item.util.ArrivalUpdateItemUtil");
	}

	function doPost(){

		if(soy2_check_token()){
			ArrivalUpdateItemUtil::saveConfig($_POST["Config"]);
			$this->configObj->redirect("updated");
		}
	}

	function execute(){
		parent::__construct();

		$config = ArrivalUpdateItemUtil::getConfig();

		$this->addForm("form");

		$this->addInput("count", array(
			"name" => "Config[count]",
			"value" => (isset($config["count"])) ? (int)$config["count"] : 5
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
