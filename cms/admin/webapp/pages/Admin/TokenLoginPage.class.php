<?php

class TokenLoginPage extends WebPage{

	function doPost(){

		if(!soy2_check_token()){
			SOY2PageController::jump("Admin.TokenLogin");
		}

		if(isset($_POST["TokenLoginMode"]) && (int)$_POST["TokenLoginMode"] === 1){
			$endPointUri = (isset($_POST["EndPointUri"]) && strlen(trim($_POST["EndPointUri"]))) ? trim($_POST["EndPointUri"]) : "";
			TokenLoginUtil::turnOnTokenLoginMode();
			TokenLoginUtil::createEndPoint($endPointUri);
		}else{
			TokenLoginUtil::turnOffTokenLoginMode();
			TokenLoginUtil::deleteEndPoint();
		}

		$d = (isset($_POST["AllowTokenLoginPeriod"]) && is_numeric($_POST["AllowTokenLoginPeriod"])) ? (int)$_POST["AllowTokenLoginPeriod"] : TokenLoginUtil::ALLOW_TOKEN_LOGIN_PERIOD_DEFAULT;
		SOY2::import("domain.admin.AdminDataSets");
		AdminDataSets::put(TokenLoginUtil::ALLOW_TOKEN_LOGIN_PERIOD_KEY, $d);
		
		SOY2PageController::jump("Admin.TokenLogin?updated");
	}

	function __construct(){
		//初期管理者のみ
		if(!UserInfoUtil::isDefaultUser()){
			SOY2PageController::jump("");
		}

		SOY2::import("util.TokenLoginUtil");

		parent::__construct();

		foreach(array("updated", "failed") as $t){
			DisplayPlugin::toggle($t, isset($_GET[$t]));
		}

		self::_buildForm();
	}

	private function _buildForm(){
		
		$this->addForm("form");

		$isTokenLoginMode = TokenLoginUtil::isTokenLoginMode();
		$this->addCheckBox("token_login_mode", array(
			"name" => "TokenLoginMode",
			"value" => 1,
			"selected" => $isTokenLoginMode,
			"label" => "パスワード無しログインを有効にする"
		));

		DisplayPlugin::toggle("token_login_mode", $isTokenLoginMode);

		$protocol = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") ? "https" : "http";
		$this->addLabel("site_url", array(
			"text" => $protocol."://".$_SERVER["HTTP_HOST"]."/"
		));

		$this->addInput("endpoint_uri", array(
			"name" => "EndPointUri",
			"value" => TokenLoginUtil::getEndpointUri(),
			"required" => true,
		));

		$this->addInput("allow_login_day_period", array(
			"name" => "AllowTokenLoginPeriod",
			"value" => TokenLoginUtil::getAllowTokenLoginPeriod(),
			"required" => true,
			"style" => "width:120px"
		));
	}
}
