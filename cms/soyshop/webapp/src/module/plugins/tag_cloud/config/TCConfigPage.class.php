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

		$tags = (isset($cnf["tags"])) ? trim($cnf["tags"]) : "";
		$this->addTextArea("tags", array(
			"name" => "Config[tags]",
			"value" => $tags,
		));

		DisplayPlugin::toggle("tag_category", strlen($tags));

		$this->addLink("tag_category_setting_link", array(
			"link" => SOY2PageController::createLink("Config.Detail?plugin=tag_cloud&category")
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
