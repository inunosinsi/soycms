<?php

class TicketBaseConfigPage extends WebPage {

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.common_ticket_base.util.TicketBaseUtil");
	}

	function doPost(){
		if(soy2_check_token() && isset($_POST["Config"])){
			TicketBaseUtil::saveConfig($_POST["Config"]);
			$this->configObj->redirect("updated");
		}
		$this->configObj->redirect("error");
	}

	function execute(){
		parent::__construct();

		DisplayPlugin::toggle("error", isset($_GET["error"]));

		$config = TicketBaseUtil::getConfig();

		$this->addForm("form");

		$this->addInput("label", array(
			"name" => "Config[label]",
			"value" => $config["label"]
		));

		$this->addInput("unit", array(
			"name" => "Config[unit]",
			"value" => $config["unit"]
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
