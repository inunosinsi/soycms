<?php
SOY2::import("util.CMSAccessRestrictionsUtil");

// 一時設定やトークンファイルの生存時間を調べて、古ければ削除
CMSAccessRestrictionsUtil::organizeConfigFiles();

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

	CMSAdminPageController::outputOnNotFound();
}