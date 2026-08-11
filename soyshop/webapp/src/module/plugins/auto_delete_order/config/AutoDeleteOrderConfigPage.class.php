<?php

class AutoDeleteOrderConfigPage extends WebPage {

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.auto_delete_order.util.AutoDeleteOrderUtil");
	}

	function doPost(){
		if(soy2_check_token()){
			AutoDeleteOrderUtil::saveConfig($_POST["Config"]);
			$this->configObj->redirect("updated");
		}
	}

	function execute(){
		parent::__construct();

		$conf = AutoDeleteOrderUtil::getConfig();

		$this->addForm("form");

		foreach(AutoDeleteOrderUtil::getTypes() as $t){
			$this->addCheckBox($t, array(
				"name" => "Config[" . $t . "]",
				"value" => 1,
				"selected" => (isset($conf[$t]) && (int)$conf[$t] === 1)
			));

			$this->addInput($t . "_timming", array(
				"name" => "Config[" . $t . "_timming]",
				"value" => (isset($conf[$t . "_timming"]) && is_numeric($conf[$t . "_timming"])) ? (int)$conf[$t . "_timming"] : 1,
				"style" => "width:60px;text-align:right"
			));
		}
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
