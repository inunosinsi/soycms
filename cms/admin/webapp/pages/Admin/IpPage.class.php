<?php

class IpPage extends WebPage{

	function doPost(){

		if(!soy2_check_token()){
			SOY2PageController::jump("Admin.Ip");
		}

		CMSAccessRestrictionsUtil::setTwoFactorAuthConfig((isset($_POST["two_factor"])));
		CMSAccessRestrictionsUtil::setRestrictIpDirectAccessConfig((isset($_POST["ip_direct"])));
		CMSAccessRestrictionsUtil::saveConfig(CMSAccessRestrictionsUtil::MODE_PERMANENT, $_POST["ipaddress"]);
		CMSAccessRestrictionsUtil::saveConfig(CMSAccessRestrictionsUtil::MODE_BLACKLIST, $_POST["blacklist"]);
		CMSAccessRestrictionsUtil::saveConfig(CMSAccessRestrictionsUtil::MODE_USER_AGENT, $_POST["blacklist_useragent"]);
		SOY2PageController::jump("Admin.Ip?updated");
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

		self::_buildNotice();
		self::_buildForm();

		$this->addLabel("remote_addr", array(
			"text" => $_SERVER["REMOTE_ADDR"]
		));
	}

	private function _buildNotice(){
		$dir = SOY2::RootDir() . "config/ip/";
		DisplayPlugin::toggle("no_auth", !is_writable($dir));

		$this->addLabel("soycms_ip_config_dir", array(
			"text" => $dir
		));
		$this->addLabel("run_user", array(
			"text" => defined("SOYCMS_PHP_CGI_MODE") && SOYCMS_PHP_CGI_MODE ? fileowner($_SERVER["SCRIPT_FILENAME"]) : "Apacheの実行ユーザー"
		));
	}

	private function _buildForm(){
		$this->addForm("form");

		$this->addTextArea("ipaddress", array(
			"name" => "ipaddress",
			"value" => CMSAccessRestrictionsUtil::readConfig(CMSAccessRestrictionsUtil::MODE_PERMANENT),
			"attr:placeholder" => "アクセスを許可したいIPアドレスを改行区切りで指定します。"
		));

		$this->addCheckBox("two_factor_auth", array(
			"name" => "two_factor",
			"value" => 1,
			"selected" => CMSAccessRestrictionsUtil::checkIsTwoFactorAuth(),
			"label" => "二段階認証を有効にする"
		));

		$this->addCheckBox("ip_direct", array(
			"name" => "ip_direct",
			"value" => 1,
			"selected" => CMSAccessRestrictionsUtil::checkIsRestrictIpDirectAccess(),
			"label" => "IPアドレスを直接入力したアクセスを禁止する"
		));
		
		$this->addTextArea("blacklist", array(
			"name" => "blacklist",
			"value" => CMSAccessRestrictionsUtil::readConfig(CMSAccessRestrictionsUtil::MODE_BLACKLIST),
			"attr:placeholder" => "アクセスを拒否したいIPアドレスを改行区切りで指定します。"
		));
		
		$this->addTextArea("blacklist_useragent", array(
			"name" => "blacklist_useragent",
			"value" => CMSAccessRestrictionsUtil::readConfig(CMSAccessRestrictionsUtil::MODE_USER_AGENT),
			"attr:placeholder" => "アクセスを拒否したいユーザーエージェントを改行区切りで指定します。"
		));
	}
}
