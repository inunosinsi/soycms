<?php

class IndexPage extends CMSHTMLPageBase{
	var $message = "";
	var $username;

	function doPost(){
		$action = SOY2ActionFactory::createInstance('LoginAction');
		$result = $action->run();

		//ログイン
		if($result->success()){
			// IPアドレスの登録のメールを送る
			SOY2::import("util.CMSAccessRestrictionsUtil");
			if(!defined("SOYCMS_CMS_NAME") && file_exists(SOY2::rootDir()."config/advanced.config.php")) include($dir . "config/advanced.config.php");
			if(!defined("SOYCMS_CMS_NAME")) define("SOYCMS_CMS_NAME", "SOY CMS");

			CMSAccessRestrictionsUtil::sendMailWithToken((string)$_POST["Auth"]["name"]);

			echo "ご本人確認のため、アカウントに登録されたメールアドレスへ認証用URLを送付しました。 メール内のリンクをクリックして、ログインを完了させてください。";
			exit;
			
			//SOY2PageController::redirect("");
		}else{
			//失敗したときは2秒待たせる
			sleep(2);
		}

		$this->message = CMSMessageManager::get("ADMIN_FAILURE_TO_LOGIN");
		$this->username = $result->getAttribute('username');
	}

	function __construct(){
		// 強制ログアウト
		if(UserInfoUtil::isLoggined()){
			UserInfoUtil::logout();
			SOY2PageController::jump("");
		}

		// ブラックリストはアクセス禁止
		SOY2::import("util.CMSAccessRestrictionsUtil");
		CMSAccessRestrictionsUtil::organizeConfigFiles();
		if(CMSAccessRestrictionsUtil::checkDenyIpAddress()){
			CMSAdminPageController::outputOnNotFound();
		}

		define("HEAD_TITLE", CMSUtil::getCMSName() . " Login");
		parent::__construct();

		//フォームの作成
		$this->addForm("AuthForm");

		$this->addInput("username", array(
			"name" => "Auth[name]",
			"value" => $this->username
		));
		$this->addInput("password", array(
			"name" => "Auth[password]",
			"value" => ""
		));

		$this->addLabel("message", array(
			"html" => $this->message,
			"visible" => strlen($this->message)
		));

		$this->addModel("biglogo", array(
    		"src" => CMSUtil::getLogoFile()
    	));
	}

    /**
     * Overwrite CMSHTMLPageBase::getTemplateFilePath
     */
    function getTemplateFilePath(){

		if(defined("SOYCMS_LANGUAGE_DIR")){
			$dir = dirname($this->getClassPath());
			if(strlen($dir) > 0) $dir .= '/';

			$soy2html_root = SOY2HTMLConfig::PageDir();
			$language_root = SOYCMS_LANGUAGE_DIR.SOY2HTMLConfig::Language() . "/Login/";
			$custom_lang_html = str_replace($soy2html_root, $language_root, $dir) . get_class($this) . ".html";
			if(file_exists($custom_lang_html)){
				return $custom_lang_html;
			}
		}

		return parent::getTemplateFilePath();
    }
}
