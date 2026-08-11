
<?php

class OAuthConfigPage extends WebPage {

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.gmail_api_oauth.util.GmailApiOAuthUtil");
	}

	function doPost(){
		if(soy2_check_token()){
			if(isset($_FILES["client_secret"]["name"])){
				$tmp = $_FILES["client_secret"]["tmp_name"];
				move_uploaded_file($tmp, GmailApiOAuthUtil::getClientSecretJsonPath());
				$this->configObj->redirect("updated");
			}
		}
	}

	function execute(){
		parent::__construct();

		$this->addForm("form", array(
			"attr:enctype" => "multipart/form-data"
		));

		$this->addLabel("callback_url", array(
			"text" => GmailApiOAuthUtil::getCallbackUrl()
		));

		$this->addLink("authenticate_button", array(
			"link" => GmailApiOAuthUtil::getCallbackUrl()	
		));

		DisplayPlugin::toggle("permission_notice", !GmailApiOAuthUtil::checkPermission());
		$this->addLabel("run_user", array(
			"text" => defined("SOYCMS_PHP_CGI_MODE") && SOYCMS_PHP_CGI_MODE ? fileowner($_SERVER["SCRIPT_FILENAME"]) : "Apacheの実行ユーザー"
		));		
		$this->addLabel("token_dir", array(
			"text" => GmailApiOAuthUtil::getTokenDir()
		));

		DisplayPlugin::toggle("is_client_secret", GmailApiOAuthUtil::checkClientSecret());

		DisplayPlugin::toggle("is_gmail_token", GmailApiOAuthUtil::checkGmailToken());
		DisplayPlugin::toggle("no_gmail_token", !GmailApiOAuthUtil::checkGmailToken());
	}

	function setConfigObj(GmailAPIOAuthConfig $configObj){
		$this->configObj = $configObj;
	}
}
