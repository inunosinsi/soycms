<?php

class AffiliateA8flyConfigFormPage extends WebPage{

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.affiliate_a8fly.util.AffiliateA8flyUtil");
	}

	function doPost(){
		if(soy2_check_token() && isset($_POST["config"])){
			AffiliateA8flyUtil::saveConfig($_POST["config"]);
			$this->configObj->redirect("updated");
		}
	}

	function execute(){
		parent::__construct();

		$config = AffiliateA8flyUtil::getConfig();

		$this->addForm("form");

		$this->addInput("program_id", array(
			"name" => "config[id]",
			"value" => (isset($config["id"])) ? $config["id"] : "",
			"style" => "ime-mode:inactive"
		));

		$this->addCheckBox("sandbox", array(
			"name" => "config[sandbox]",
			"value" => 1,
			"selected" => (isset($config["sandbox"])) ? $config["sandbox"] : "",
			"elementId" => "sandbox"
		));
	}

	function setConfigObj($obj) {
		$this->configObj = $obj;
	}
}
