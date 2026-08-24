<?php
class IndexPage extends CMSWebPageBase{

	function doPost(){
		if(soy2_check_token()){
			$cfg = (isset($_POST["Config"])) ? $_POST["Config"] : array();
			if(!isset($cfg["on"]) || $cfg["on"] != 1) $cfg["on"] = 0;

			AccessLogUtil::saveConfig($cfg);
		
			$this->jump("NotFound");
		}
	}

	function __construct(){
		if(!UserInfoUtil::isDefaultUser()){
    		$this->jump("");
		}

		SOY2::import("util.AccessLogUtil");
		
		parent::__construct();

		DisplayPlugin::toggle("permission_notice", !AccessLogUtil::checkPermission());
		$this->addLabel("run_user", array(
			"text" => defined("SOYCMS_PHP_CGI_MODE") && SOYCMS_PHP_CGI_MODE ? fileowner($_SERVER["SCRIPT_FILENAME"]) : "Apacheの実行ユーザー"
		));		
		$this->addLabel("log_dir", array(
			"text" => AccessLogUtil::getLogDir()
		));

		// 集計を開始している場合
		$isLogging = AccessLogUtil::isLoggingOn();
		DisplayPlugin::toggle("is_logging", $isLogging);

		$this->addForm("form");

		$this->addCheckBox("loggin_on", array(
			"name" => "Config[on]",
			"value" => 1,
			"selected" => $isLogging,
			"label" => "404NotFoundページのアクセスログの収集を開始する"	
		));

		$this->addInput("log_count", array(
			"name" => "Config[count]",
			"value" => AccessLogUtil::getLogCount(),
			"style" => "width:80px"
		));

		$this->createAdd("log_list", "_common.Log.NotFoundLogListComponent", array(
			"list"	=> ($isLogging) ? SOY2DAOFactory::create("admin.NotFoundLogDAO")->get() : array()
		));
	}
}
