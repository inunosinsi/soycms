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
define("CMS_LABEL_ICON_DIRECTORY_URL",SOY2PageController::createRelativeLink("./image/labelicon/"));
define("CMS_PAGE_ICON_DIRECTORY",dirname(dirname(__FILE__))."/image/pageicon/");
define("CMS_PAGE_ICON_DIRECTORY_URL",SOY2PageController::createRelativeLink("./image/pageicon/"));

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
	
	if(!UserInfoUtil::getSite()){
		//DefaultLogin用にアクセスURIを渡す
		SOY2PageController::redirect("../admin/?r=".rawurlencode(SOY2PageController::createRelativeLink($_SERVER["REQUEST_URI"])));
	}
	
	SOY2ActionConfig::ActionDir(SOY2ActionConfig::ActionDir()."/site/");
	SOY2HTMLConfig::PageDir(dirname(__FILE__)."/pages/");
	
	switch(SOYCMS_DB_TYPE){
		
		case "mysql":
			SOY2DAOConfig::Dsn(UserInfoUtil::getSite()->getDataSourceName());
			SOY2DAOConfig::user(ADMIN_DB_USER);
			SOY2DAOConfig::pass(ADMIN_DB_PASS);
			break;
			
		case "sqlite":
		default:
			SOY2DAOConfig::Dsn("sqlite:".UserInfoUtil::getSiteDirectory().".db/sqlite.db");
					
			break;
	}
}

//エントリー管理者がアクセスしていいパスのチェック
if(! UserInfoUtil::hasSiteAdminRole()){
	if(defined("SOYCMS_ASP_MODE")){
	}else{
		if(! SOY2Logic::createInstance("logic.site.Filter.EntryAdministratorFilterLogic")->checkAvaiable()){
			SOY2PageController::jump("Simple");	//トップページに移動
		}
	}
}

//スクリプト、CSSの読み込み
$scriptRoot = SOY2PageController::createRelativeLink("js/");

//blueprintのCSS
HTMLHead::addLink("blueprint_print",array(
	"rel" => "stylesheet",
	"type" => "text/css",
	"media" => "print",
	"href" => SOY2PageController::createRelativeLink("./css/blueprint/print.css")."?".SOYCMS_BUILD_TIME
));

HTMLHead::addLink("blueprint_screen",array(
	"rel" => "stylesheet",
	"type" => "text/css",
	"media" => "screen, projection",
	"href" => SOY2PageController::createRelativeLink("./css/blueprint/screen.css")."?".SOYCMS_BUILD_TIME
));


//共通のCSS
HTMLHead::addLink("common",array(
	"type" => "text/css",
	"rel" => "stylesheet",
	"href" => SOY2PageController::createRelativeLink("css/style.css")."?".SOYCMS_BUILD_TIME
));

//table用CSS
HTMLHead::addLink("table",array(
	"type" => "text/css",
	"rel" => "stylesheet",
	"href" => SOY2PageController::createRelativeLink("./css/table.css")."?".SOYCMS_BUILD_TIME
));

HTMLHead::addLink("form",array(
	"rel" => "stylesheet",
	"type" => "text/css",
	"href" => SOY2PageController::createRelativeLink("./css/form.css")."?".SOYCMS_BUILD_TIME
));

HTMLHead::addLink("toolbox",array(
	"rel" => "stylesheet",
	"type" => "text/css",
	"href" => SOY2PageController::createRelativeLink("./css/toolbox/toolbox.css")."?".SOYCMS_BUILD_TIME
));

//共通スクリプト
HTMLHead::addScript("jquery.js",array(
	"src" => SOY2PageController::createRelativeLink("js/jquery.js")."?".SOYCMS_BUILD_TIME
));
HTMLHead::addScript("jquery_ui.js",array(
	"src" => SOY2PageController::createRelativeLink("js/jquery-ui.min.js")."?".SOYCMS_BUILD_TIME
));

HTMLHead::addScript("common",array(
	"src" => $scriptRoot."common.js"."?".SOYCMS_BUILD_TIME
));

if(!defined("SOYCMS_ASP_MODE")){
	HTMLHead::addScript("site_check",array(
		"script" => "soycms_check_site('".UserInfoUtil::getSite()->getId()."','".SOY2PageController::createLink("Common.Check")."');"
	));
}

define("SOYCMS_LANGUAGE_DIR",dirname(__FILE__) . "/language/");

//言語判断
switch(SOYCMS_LANGUAGE){
	case "en":
		HTMLHead::addScript("lang",array("src" => $scriptRoot."lang/en.js"));
		SOY2::import("lang.en",".php");
		break;
	case "ja":
	default:
		HTMLHead::addScript("lang",array("src" => $scriptRoot."lang/ja.js"));
		SOY2::import("lang.ja",".php");
		break;
}

//update event
SOY2DAOConfig::setUpdateQueryEvent(create_function('$sql,$binds','touch("'.UserInfoUtil::getSiteDirectory().'.db/'.SOYCMS_DB_TYPE.'.db");'));
?>