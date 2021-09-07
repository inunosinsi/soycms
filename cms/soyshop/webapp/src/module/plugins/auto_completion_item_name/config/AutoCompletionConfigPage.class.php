<?php

class AutoCompletionConfigPage extends WebPage {

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.auto_completion_item_name.util.AutoCompletionUtil");
	}

	function doPost(){
		if(soy2_check_token()){
			AutoCompletionUtil::saveConfig($_POST["Config"]);
			$this->configObj->redirect("updated");
		}
	}

	function execute(){
		parent::__construct();

		$cnf = AutoCompletionUtil::getConfig();

		$this->addForm("form");

		$this->addInput("candidate_output_count", array(
			"name" => "Config[count]",
			"value" => (isset($cnf["count"])) ? (int)$cnf["count"] : 10,
			"style" => "width:80px;"
		));

	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
