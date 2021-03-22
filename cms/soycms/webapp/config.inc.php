<?php
/**
 * PageController
 * SOY2PageControllerを使う前にinitする必要がある
 */
SOY2::import("base.CMSAdminPageController");
SOY2PageController::init("CMSAdminPageController");

SOY2HTMLConfig::CacheDir(dirname(dirname(__FILE__))."/cache/");
SOY2DAOConfig::DaoCacheDir(dirname(dirname(__FILE__))."/cache/");

//アイコンのディレクトリ
define("CMS_LABEL_ICON_DIRECTORY",dirname(dirname(__FILE__))."/image/labelicon/");
define("CMS_LABEL_ICON_DIRECTORY_URL",SOY2PageController::createRelativeLink("../soycms/image/labelicon/"));
define("CMS_PAGE_ICON_DIRECTORY",dirname(dirname(__FILE__))."/image/pageicon/");
define("CMS_PAGE_ICON_DIRECTORY_URL",SOY2PageController::createRelativeLink("../soycms/image/pageicon/"));

//必須コンポーネントのimport
SOY2::import("base.CMSWebPageBase");
SOY2::import("base.CMSFormBase");
SOY2::import("base.MessagePlugin");
SOY2::import("base.CustomPlugin");
SOY2::import("base.EntryPagerComponent");
SOY2::imports("base.validator.*");
SOY2::import("domain.admin.Site");
SOY2::import("domain.cms.SiteConfig");
SOY2::import("util.CMSToolBox");
SOY2::import("util.CMSMessageManager");
SOY2::import("util.CMSPlugin");
SOY2::import("util.CMSUtil");
SOY2::import("util.SOYAppUtil");
SOY2::import("util.ServerInfoUtil");
SOY2::import("util.UserInfoUtil");
SOY2::import("lib.SOYCMSEmojiUtil");	//絵文字用のUtility

//メッセージのディレクトリ
CMSMessageManager::addMessageDirectoryPath(CMS_SOYBOY_MESSAGE_DIR);
CMSMessageManager::addMessageDirectoryPath(CMS_HELP_MESSAGE_DIR);
CMSMessageManager::addMessageDirectoryPath(CMS_CONTROLPANEL_MESSAGE_DIR);

//言語別テンプレートディレクトリ
define("SOYCMS_LANGUAGE_DIR",dirname(__FILE__) . "/language/");

//ログインチェック
if(!UserInfoUtil::isLoggined()){
	if(defined("SOYCMS_ASP_MODE")){
		SOY2HTMLConfig::PageDir(dirname(__FILE__)."/pages/Login/");
		SOY2ActionConfig::ActionDir(SOY2ActionConfig::ActionDir()."login/");
		SOY2DAOConfig::Dsn(SOYCMS_ASP_DSN);
		SOY2DAOConfig::user(SOYCMS_ASP_USER);
		SOY2DAOConfig::pass(SOYCMS_ASP_PASS);
	}else{
		SOY2PageController::redirect("../admin/?r=".rawurlencode(SOY2PageController::createRelativeLink($_SERVER["REQUEST_URI"])));
	}
}else{
	$currentSite = UserInfoUtil::getSite();
	if(!$currentSite instanceof Site || !is_numeric($currentSite->getId())){
		//DefaultLogin用にアクセスURIを渡す
		SOY2PageController::redirect("../admin/?r=".rawurlencode(SOY2PageController::createRelativeLink($_SERVER["REQUEST_URI"])));
	}

	if($currentSite->getSiteType() == Site::TYPE_SOY_SHOP){
		SOY2PageController::redirect("../admin/index.php/Site/Login/0?site_id=".UserInfoUtil::getSite()->getSiteId());
	}

	SOY2ActionConfig::ActionDir(SOY2ActionConfig::ActionDir()."/site/");
	SOY2HTMLConfig::PageDir(dirname(__FILE__)."/pages/");

	switch(SOYCMS_DB_TYPE){
		case "mysql":
			SOY2DAOConfig::Dsn($currentSite->getDataSourceName());
			SOY2DAOConfig::user(ADMIN_DB_USER);
			SOY2DAOConfig::pass(ADMIN_DB_PASS);
			break;
		case "sqlite":
		default:
			SOY2DAOConfig::Dsn($currentSite->getDataSourceName());
			//SOY2DAOConfig::Dsn("sqlite:".UserInfoUtil::getSiteDirectory().".db/sqlite.db");
			break;
	}
	unset($currentSite);

	//初期管理者とそれ以外で表示を変える
	DisplayPlugin::toggle("for_default_user", UserInfoUtil::isDefaultUser());
	DisplayPlugin::toggle("for_not_default_user", !UserInfoUtil::isDefaultUser());

	//一般管理者権限の有無で表示を変える
	DisplayPlugin::toggle("for_site_administrator", UserInfoUtil::hasSiteAdminRole());
	DisplayPlugin::toggle("for_not_site_administrator", !UserInfoUtil::hasSiteAdminRole());

	//記事公開権限の有無で表示を変える
	DisplayPlugin::toggle("for_entry_publisher", UserInfoUtil::hasEntryPublisherRole());
	DisplayPlugin::toggle("for_entry_writer", !UserInfoUtil::hasEntryPublisherRole());

	//記事管理者がアクセスしていいパスのチェック
	if(! UserInfoUtil::hasSiteAdminRole()){
		if(defined("SOYCMS_ASP_MODE")){
			//
		}else{
			if(! SOY2Logic::createInstance("logic.site.Filter.EntryAdministratorFilterLogic")->checkAvaiable()){
				SOY2PageController::jump("Simple");	//トップページに移動
			}
		}
	}
}

//update event
SOY2DAOConfig::setUpdateQueryEvent(function($sql,$binds) { touch(UserInfoUtil::getSiteDirectory().".db/".SOYCMS_DB_TYPE.".db"); });
