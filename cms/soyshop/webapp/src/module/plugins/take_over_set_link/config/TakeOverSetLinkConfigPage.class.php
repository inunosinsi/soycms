<?php

class TakeOverSetLinkConfigPage extends WebPage {

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.take_over_set_link.util.TakeOverLinkUtil");
	}

	function doPost(){
		if(soy2_check_token()){
			TakeOverLinkUtil::saveConfig($_POST["Config"]);
			$this->configObj->redirect("updated");
		}
	}

	function execute(){
		parent::__construct();

		$cnf = TakeOverLinkUtil::getConfig();

		$this->addForm("form");

		$this->addInput("url", array(
			"name" => "Config[url]",
			"value" => $cnf["url"],
			"style" => "width:60%;"
		));

		$this->addInput("timeout", array(
			"name" => "Config[timeout]",
			"value" => (is_numeric($cnf["timeout"])) ? $cnf["timeout"] : TakeOverLinkUtil::TIMEOUT,
			"style" => "width:80px;"
		));

		$this->addTextArea("description", array(
			"name" => "Config[description]",
			"value" => $cnf["description"]
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
