<?php

class BulkChangeConfigPage extends WebPage {

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.itemorder_price_bulk_change.util.BulkChangeUtil");
	}

	function doPost(){
		if(soy2_check_token()){
			BulkChangeUtil::saveConfig($_POST["Config"]);
			$this->configObj->redirect("updated");
		}
	}

	function execute(){
		parent::__construct();

		$config = BulkChangeUtil::getConfig();

		$this->addForm("form");

		foreach(BulkChangeUtil::getModeList() as $t){
			$this->addCheckBox("mode_" . $t, array(
				"name" => "Config[mode]",
				"value" => $t,
				"selected" => ($t == $config["mode"]),
				"label" => BulkChangeUtil::getModeText($t)
			));
		}

		foreach(BulkChangeUtil::getMethodList() as $t){
			$this->addCheckBox("method_" . $t, array(
				"name" => "Config[method]",
				"value" => $t,
				"selected" => ($t == $config["method"]),
				"label" => BulkChangeUtil::getMethodText($t)
			));
		}
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
