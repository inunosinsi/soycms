<?php

class ReserveCalendarConfigFormPage extends WebPage{

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.reserve_calendar.util.ReserveCalendarUtil");
	}

	function doPost(){
		if(soy2_check_token() && isset($_POST["Config"])){
			ReserveCalendarUtil::saveConfig($_POST["Config"]);
			$this->configObj->redirect("updated");
		}
	}

	function execute(){
		parent::__construct();

		$config = ReserveCalendarUtil::getConfig();

		$this->addForm("form");

		$this->addCheckBox("is_tmp_order", array(
			"name" => "Config[tmp]",
			"value" => ReserveCalendarUtil::IS_TMP,
			"selected" => (!isset($config["tmp"]) || $config["tmp"] == ReserveCalendarUtil::IS_TMP),
			"label" => "仮登録を行う(β版)"
		));

		$this->addCheckBox("no_tmp_order", array(
			"name" => "Config[tmp]",
			"value" => ReserveCalendarUtil::NO_TMP,
			"selected" => (isset($config["tmp"]) && $config["tmp"] == ReserveCalendarUtil::NO_TMP),
			"label" => "仮登録を行わない"
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
