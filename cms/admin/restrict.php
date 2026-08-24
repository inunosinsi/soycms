<?php
SOY2::import("util.CMSAccessRestrictionsUtil");

// 一時設定やトークンファイルの生存時間を調べて、古ければ削除
CMSAccessRestrictionsUtil::organizeConfigFiles();

// IPアドレス直接入力によるアクセスを禁止
if(CMSAccessRestrictionsUtil::checkIsRestrictIpDirectAccess()){
	if(filter_var($_SERVER['HTTP_HOST'], FILTER_VALIDATE_IP)){
		CMSAdminPageController::outputOnNotFound();
	}
}

// IPアドレスによるアクセス制限
if(!CMSAccessRestrictionsUtil::checkAllowIpAddress()){
	$dir = dirname(dirname(__FILE__)) . "/common/";
	if(!defined("SOYCMS_CMS_NAME") && file_exists($dir . "config/advanced.config.php")) include($dir . "config/advanced.config.php");
	if(!defined("SOYCMS_CMS_NAME")) define("SOYCMS_CMS_NAME", "SOY CMS");

	// IPアドレスの一時設定
	if(isset($_GET[CMSAccessRestrictionsUtil::UNLOCK_KEY])) CMSAccessRestrictionsUtil::sendMailWithToken($_GET[CMSAccessRestrictionsUtil::UNLOCK_KEY]);

	// トークンからIPアドレスの一時設定を行う
	if(isset($_GET) && count($_GET) && CMSAccessRestrictionsUtil::checkIsToken($_GET)){
		CMSAccessRestrictionsUtil::setTemporaryConfig();
		CMSAdminPageController::jump("");
	}

	// ログインしていたら強制ログアウト
	if(UserInfoUtil::isLoggined()){
		UserInfoUtil::logout();
	}

	//2段階認証
	if(CMSAccessRestrictionsUtil::checkIsTwoFactorAuth()){
		//何もしない
	}else{
		CMSAdminPageController::outputOnNotFound();
	}
}

// ユーザーエージェントによるアクセス制限
if(!CMSAccessRestrictionsUtil::checkAllowUserAgent()){
	CMSAdminPageController::outputOnNotFound();
}
