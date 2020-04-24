<?php

class AddMailTypeRemovePage extends WebPage{

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.common_add_mail_type.util.AddMailTypeUtil");
	}

	function execute(){
		if(soy2_check_token() && isset($_GET["field_id"])){

			$configs = AddMailTypeUtil::getConfig($_GET["mode"]);
			unset($configs[$_GET["field_id"]]);
			AddMailTypeUtil::saveConfig($configs, $_GET["mode"]);

			$this->configObj->redirect("removed");
		}

		$this->configObj->redirect("failed");
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
