<?php

class OrderStatusInvalidConfigPage extends WebPage{

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.change_order_status_invalid.util.ChangeOrderStatusInvalidUtil");
	}

	function doPost(){
		if(soy2_check_token()){
			ChangeOrderStatusInvalidUtil::saveConfig($_POST["Config"]);
			$this->configObj->redirect("updated");
		}
		$this->configObj->redirect("failed");
	}

	function execute(){
		parent::__construct();

		DisplayPlugin::toggle("failed", isset($_GET["failed"]));

		$cnf = ChangeOrderStatusInvalidUtil::getConfig();

		$this->addForm("form");

		$this->addInput("minute", array(
			"name" => "Config[minute]",
			"value" => (isset($cnf["minute"]) && is_numeric($cnf["minute"])) ? (int)$cnf["minute"] : 5,
			"style" => "width:80px;"
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
