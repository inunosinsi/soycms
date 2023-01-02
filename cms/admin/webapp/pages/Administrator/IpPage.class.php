<?php

class IpPage extends WebPage{

	function doPost(){

		if(!soy2_check_token()){
			SOY2PageController::jump("Administrator.Ip");
		}
		
		CMSAccessRestrictionsUtil::saveConfig(CMSAccessRestrictionsUtil::MODE_PERMANENT, $_POST["ipaddress"]);
		SOY2PageController::jump("Administrator.Ip?updated");
	}

	function __construct(){
		//初期管理者のみ
		if(!UserInfoUtil::isDefaultUser()){
			SOY2PageController::jump("");
		}

		SOY2::import("util.CMSAccessRestrictionsUtil");

		parent::__construct();

		foreach(array("updated", "failed") as $t){
			DisplayPlugin::toggle($t, isset($_GET[$t]));
		}

		$hasMailConfig = (strlen(SOY2LogicContainer::get("logic.mail.MailConfigLogic")->get()->getFromMailAddress()) > 0);
		$hasMailAddress = (SOY2Logic::createInstance("logic.admin.Administrator.AdministratorLogic")->hasMailaddress());
		$isValid = ($hasMailConfig && $hasMailAddress);
		DisplayPlugin::toggle("valid", $isValid);
		DisplayPlugin::toggle("no_valid", !$isValid);

		self::_buildForm();

		$this->addLabel("remote_addr", array(
			"text" => $_SERVER["REMOTE_ADDR"]
		));
	}

	private function _buildForm(){
		$this->addForm("form");

		$this->addTextArea("ipaddress", array(
			"name" => "ipaddress",
			"value" => CMSAccessRestrictionsUtil::readConfig(CMSAccessRestrictionsUtil::MODE_PERMANENT),
			"attr:placeholder" => "アクセスを許可したいIPアドレスを改行区切りで指定します。"
		));
	}
}
