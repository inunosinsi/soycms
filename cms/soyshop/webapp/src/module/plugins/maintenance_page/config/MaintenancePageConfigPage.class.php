<?php

class MaintenancePageConfigPage extends WebPage {

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.maintenance_page.util.MaintenancePageUtil");
	}

	function doPost(){
		if(soy2_check_token()){
			$cnf = (isset($_POST["Config"]) && is_array($_POST["Config"])) ? $_POST["Config"] : array();
			MaintenancePageUtil::saveConfig($cnf);
			$this->configObj->redirect("updated");
		}
	}

	function execute(){
		parent::__construct();

		DisplayPlugin::toggle("is_maintenance_page", MaintenancePageUtil::isMaintenancePage());

		$cnf = MaintenancePageUtil::getConfig();

		$this->addForm("form");

		$this->addCheckBox("maintenance_on", array(
			"name" => "Config[on]",
			"value" => 1,
			"selected" => (isset($cnf["on"]) && is_numeric($cnf["on"]) && (int)$cnf["on"] === 1),
			"label" => "公開側でメンテナンスページを設置する"
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
