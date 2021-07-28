<?php

class TCConfigPage extends WebPage {

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.tag_cloud.util.TagCloudUtil");
	}

	function doPost(){
		if(soy2_check_token()){
			TagCloudUtil::saveConfig($_POST["Config"]);
			$this->configObj->redirect("updated");
		}
	}

	function execute(){
		parent::__construct();

		$cnf = TagCloudUtil::getConfig();

		$this->addForm("form");

		$this->addInput("rank_divide", array(
			"name" => "Config[divide]",
			"value" => (isset($cnf["divide"])) ? (int)$cnf["divide"] : 10,
			"style" => "width:80px;"
		));

		$this->addTextArea("tags", array(
			"name" => "Config[tags]",
			"value" => (isset($cnf["tags"])) ? $cnf["tags"] : "",
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
