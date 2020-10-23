<?php
SOY2::import("module.plugins.download_assistant.common.DownloadAssistantCommon");
class DownloadConfigPage extends WebPage{

	function doPost(){

		if(soy2_check_token()){
			$config = $_POST["Config"];
			$config["timeLimit"] = soyshop_convert_number($config["timeLimit"], null);
			$config["count"] = soyshop_convert_number($config["count"], null);

			$config["allow"] = (isset($config["allow"])) ? 1 : null;
			$config["mail"] = (isset($config["mail"])) ? $config["mail"] : $this->text;

			SOYShop_DataSets::put("download_assistant.config", $config);
		}

		SOY2PageController::jump("Config.DownloadConfig?updated");

	}

    function __construct(){

    	parent::__construct();

    	$config = DownloadAssistantCommon::getConfig();
    	$commonLogic = SOY2Logic::createInstance("module.plugins.download_assistant.logic.DownloadCommonLogic");

    	$this->addForm("form");

    	$this->addInput("download_limit_time", array(
    		"name" => "Config[timeLimit]",
    		"value" => (isset($config["timeLimit"])) ? $config["timeLimit"] : ""
    	));

    	$this->addInput("download_count", array(
    		"name" => "Config[count]",
    		"value" => (isset($config["count"])) ? $config["count"] : ""
    	));

    	$this->addCheckBox("download_mail_allow", array(
    		"name" => "Config[allow]",
    		"value" => 1,
    		"selected" => (isset($config["allow"]) && $config["allow"] == 1),
    		"elementId" => "download_mail_allow"
    	));

    	$this->addModel("is_download_mail", array(
    		"visible" => (isset($config["allow"]) && $config["allow"] == 1)
    	));

    	$this->addTextArea("download_mail", array(
    		"name" => "Config[mail]",
    		"value" => (isset($config["mail"])) ? $config["mail"] : ""
    	));

    	$this->addLabel("download_allow_extension", array(
    		"html" => $commonLogic->allowExtension()
    	));
    }

	function getBreadcrumb(){
		return BreadcrumbComponent::build("ダウンロード販売の設定", array("Config" => "設定"));
	}
}
