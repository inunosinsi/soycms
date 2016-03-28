<?php
include("../common/common.inc.php");
SOY2HTMLConfig::CacheDir(dirname(__FILE__)."/cache/");
SOY2HTMLConfig::PageDir(dirname(__FILE__)."/webapp/pages/");

SOY2DAOConfig::Dsn(ADMIN_DB_DSN);
SOY2DAOConfig::user(ADMIN_DB_USER);
SOY2DAOConfig::pass(ADMIN_DB_PASS);

//必須コンポーネントのimport
SOY2::import("base.CMSWebPageBase");
SOY2::import("base.CMSFormBase");
SOY2::import("base.MessagePlugin");
SOY2::import("domain.admin.Site");
SOY2::import("domain.cms.SiteConfig");
SOY2::import("util.CMSToolBox");
SOY2::import("util.CMSMessageManager");
SOY2::import("util.CMSPlugin");
SOY2::import("util.CMSUtil");
SOY2::import("util.ServerInfoUtil");
SOY2::import("util.UserInfoUtil");
SOY2::import("util.SOYShopUtil");


//メッセージのディレクトリ
CMSMessageManager::addMessageDirectoryPath(CMS_SOYBOY_MESSAGE_DIR);
CMSMessageManager::addMessageDirectoryPath(CMS_HELP_MESSAGE_DIR);
CMSMessageManager::addMessageDirectoryPath(CMS_CONTROLPANEL_MESSAGE_DIR);


define("SOYCMS_LANGUAGE_DIR",str_replace("\\", "/", dirname(__FILE__) . "/webapp/language/"));

//すでにdbファイルがあればログインページに飛ばす
if(ADMIN_DB_EXISTS == true){
	SOY2PageController::redirect("./index.php");
}

try{
	$webPage = SOY2HTMLFactory::createInstance("_init.InitPage");
	$webPage->display();
}catch(Exception $e){
	$exception = $e;
	include_once(SOY2::RootDir() . "error/admin.php");
}

?>