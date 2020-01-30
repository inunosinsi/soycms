<?php

class DropboxBackupConfigPage extends WebPage{

	private $pluginObj;

	function __construct(){
		SOY2::import("domain.cms.DataSets");
	}

	function doPost(){
		if(soy2_check_token()){
			DataSets::put("dropbox_backup.token", trim($_POST["token"]));
			CMSPlugin::redirectConfigPage();
		}
	}

	function execute(){
		parent::__construct();

		$this->addForm("form");

		$this->addInput("token", array(
			"name" => "token",
			"value" => DataSets::get("dropbox_backup.token", null)
		));

		$this->addLabel("cmd", array(
			"text" => "php " . dirname(dirname(__FILE__)) . "/job/backup.php " . UserInfoUtil::getSite()->getSiteId()
		));
	}

	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}
