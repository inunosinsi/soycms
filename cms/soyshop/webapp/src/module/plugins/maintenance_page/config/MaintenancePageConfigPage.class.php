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

		$timeCnf = (isset($cnf["timming"]) && is_array($cnf["timming"])) ? $cnf["timming"] : array();
		$this->addCheckBox("maintenance_timming_set", array(
			"name" => "Config[timming][on]",
			"value" => 1,
			"selected" => (isset($timeCnf["on"]) && is_numeric($timeCnf["on"]) && (int)$timeCnf["on"] === 1),
			"label" => "時限設定を利用する"
		));

		$this->addInput("maintenance_timming_date", array(
			"name" => "Config[timming][date]",
			"value" => (isset($timeCnf["date"])) ? $timeCnf["date"] : ""
		));

		$this->addInput("maintenance_timming_time", array(
			"name" => "Config[timming][time]",
			"value" => (isset($timeCnf["time"])) ? $timeCnf["time"] : ""
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
