<?php
//クライアント側からの設定ファイル

/* こっからcommon.inc.php のコピー */
//PHPの設定
include_once(dirname(__FILE__)."/config/php.config.php");

//SOY2のinclude
include_once("lib/soy2_build.php");
include_once("lib/magic_quote_gpc.php");
include_once("lib/json_lib.php");


//CGIモードの判断
define("SOYCMS_PHP_CGI_MODE",( stripos(php_sapi_name(), "cgi") !== false ));

//設定ファイルのinclude
if(file_exists(dirname(__FILE__)."/config/custom.config.php")){
	//開発用orカスタマイズ用設定ファイル（config/custom.config.php）があればそっちを読み込む
	include_once(dirname(__FILE__)."/config/custom.config.php");
}else{
	//標準設定ファイル
	include_once("soycms.config.php");
}

//共通ソースコード
SOY2::RootDir(dirname(__FILE__)."/");

//SOY2DAOの設定
SOY2ActionConfig::ActionDir(dirname(__FILE__)."/action/");
SOY2DAOConfig::DaoDir(dirname(__FILE__)."/domain/");
SOY2DAOConfig::EntityDir(dirname(__FILE__)."/domain/");
SOY2DAOConfig::setOption("connection_failure","throw");
//SOY2DAOConfig::DaoCacheDir(dirname(dirname(__FILE__))."/cache_dao/");

//SOY2HTMLの設定
SOY2HTMLPlugin::addPlugin("page","PagePlugin");
SOY2HTMLPlugin::addPlugin("link","LinkPlugin");
SOY2HTMLPlugin::addPlugin("src","SrcPlugin");
SOY2HTMLPlugin::addPlugin("display","DisplayPlugin");
SOY2HTMLPlugin::addPlugin("panel","PanelPlugin");
SOY2HTMLPlugin::addPlugin("message","MessagePlugin");
SOY2HTMLPlugin::addPlugin("custom","CustomPlugin");

//プラグインのディレクトリ
define("CMS_BLOCK_DIRECTORY",	dirname(__FILE__)."/site_include/block/");
define("CMS_PAGE_DIRECTORY",	dirname(__FILE__)."/site_include/page/");
define("CMS_PAGE_PLUGIN",		dirname(__FILE__)."/site_include/plugin/");

//設定の読み込み
//ユーザの設定ファイル
if(file_exists(dirname(__FILE__)."/config/user.config.php")){
	include_once(dirname(__FILE__)."/config/user.config.php");
}
include_once(SOY2::RootDir()."config/normal.php");
include_once(SOY2::RootDir()."config/db/".SOYCMS_DB_TYPE.".php");

/* ここまでcommon.inc.phpのコピー */

/* サイトIDを定義する */
define("_SITE_ID_",substr(_SITE_ROOT_,strrpos(_SITE_ROOT_,DIRECTORY_SEPARATOR)+1));

//Utilty
SOY2::import('util.CMSUtil');
SOY2::import('util.CMSPlugin');

//site_include
SOY2::import('site_include.CMSPage');
SOY2::import('site_include.CMSBlogPage');
SOY2::import('site_include.CMSMobilePage');
SOY2::import('site_include.CMSApplicationPage');
SOY2::import('site_include.CMSPageLinkPlugin');
SOY2::import('site_include.CMSPagePluginBase');
SOY2::import('site_include.CMSLabel');
SOY2::import('site_include.CMSPageController');
SOY2::import('site_include.DateLabel');

if(defined("SOYCMS_ALLOW_PHP_SCRIPT")){
	define("SOY2HTML_ALLOW_PHP_SCRIPT",SOYCMS_ALLOW_PHP_SCRIPT);
}else{
	define("SOY2HTML_ALLOW_PHP_SCRIPT",false);
}

if(defined("SOYCMS_ASP_MODE")){
	$_SERVER["SCRIPT_NAME"] = "";
	$_SERVER["DOCUMENT_ROOT"] = _SITE_ROOT_;
}

SOY2HTMLConfig::CacheDir(_SITE_ROOT_."/.cache/");
SOY2DAOConfig::DaoCacheDir(_SITE_ROOT_."/.cache/");

?>