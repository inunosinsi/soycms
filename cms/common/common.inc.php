<?php
/*
 * 共通の設定を記述
 */

//現在時刻
define("SOYCMS_NOW",time());

//PHPの設定
include_once(dirname(__FILE__) . "/config/php.config.php");

//SOY2のinclude
if(file_exists(__DIR__."/lib/soy2_build.min.php")){
	include_once("lib/soy2_build.min.php");
}else{
	include_once("lib/soy2_build.php");
}

include_once("lib/magic_quote_gpc.php");
include_once("lib/json_lib.php");

//CGIモードの判断
define("SOYCMS_PHP_CGI_MODE", (stripos(php_sapi_name(), "cgi") !== false ));

//設定ファイルのinclude
if(file_exists(dirname(__FILE__) . "/config/custom.config.php")){
	//開発用orカスタマイズ用設定ファイル（config/custom.config.php）があればそっちを読み込む
	include_once(dirname(__FILE__) . "/config/custom.config.php");
}else{
	//標準設定ファイル
	include_once("soycms.config.php");
}

//共通ソースコード
SOY2::RootDir(dirname(__FILE__) . "/");

//SOY2DAOの設定
SOY2ActionConfig::ActionDir(dirname(__FILE__) . "/action/");
SOY2DAOConfig::DaoDir(dirname(__FILE__) . "/domain/");
SOY2DAOConfig::EntityDir(dirname(__FILE__) . "/domain/");
SOY2DAOConfig::setOption("connection_failure", "throw");
if(defined("SOYCMS_VERSION")) SOY2DAOConfig::setOption("cache_prefix", SOYCMS_VERSION . "_");

//SQLのディレクトリ
define("CMS_SQL_DIRECTORY", str_replace("\\", "/", dirname(__FILE__) . "/sql/"));

//SOY2HTMLの設定
if(defined("SOYCMS_VERSION")) SOY2HTMLConfig::setOption("cache_prefix", SOYCMS_VERSION . "_");
SOY2HTMLConfig::setOption("output_html", true);
SOY2HTMLPlugin::addPlugin("page", "PagePlugin");
SOY2HTMLPlugin::addPlugin("link", "LinkPlugin");
SOY2HTMLPlugin::addPlugin("src", "SrcPlugin");
SOY2HTMLPlugin::addPlugin("display", "DisplayPlugin");
SOY2HTMLPlugin::addPlugin("panel", "PanelPlugin");
SOY2HTMLPlugin::addPlugin("message", "MessagePlugin");
SOY2HTMLPlugin::addPlugin("custom", "CustomPlugin");

//プラグインのディレクトリ
define("CMS_BLOCK_DIRECTORY",	dirname(__FILE__) . "/site_include/block/");
define("CMS_PAGE_DIRECTORY",	dirname(__FILE__) . "/site_include/page/");
define("CMS_PAGE_PLUGIN",		dirname(__FILE__) . "/site_include/plugin/");
define("CMS_PAGE_PLUGIN_ADMIN_MODE", true);

//サイト側includeのファイル
define("CMS_SITE_INCLUDE", str_replace("\\", "/", dirname(__FILE__) . "/site.inc.php"));

//ユーザの設定ファイル
if(file_exists(dirname(__FILE__) . "/config/user.config.php")){
	include_once(dirname(__FILE__) . "/config/user.config.php");
}

//設定ファイルの切り替え
if(defined("SOYCMS_ASP_MODE")){
	switch(SOYCMS_ASP_MODE){
		case "release":
			include_once(SOY2::RootDir() . "config/asp/release.php");
			break;
		case "test":
			include_once(SOY2::RootDir() . "config/asp/test.php");
			break;
		case "develop":
		default:
			include_once(SOY2::RootDir() . "config/asp/develop.php");
			break;
	}

	SOY2::import("base.ASPSOY2DAO");
}else{
	include_once(SOY2::RootDir() . "config/normal.php");

	if(file_exists(SOY2::RootDir() . "config/db/" . SOYCMS_DB_TYPE . ".php")){
		include_once(SOY2::RootDir() . "config/db/" . SOYCMS_DB_TYPE . ".php");
	}else{
		include_once(SOY2::RootDir() . "error/db.php");
		exit;
	}
}

//func
include_once(SOY2::RootDir() . "site_include/func/dao.php");
include_once(SOY2::RootDir() . "site_include/func/common.php");

//言語設定：ディフォルトは日本語
if(!defined("SOYCMS_LANGUAGE")) define("SOYCMS_LANGUAGE","ja");
SOY2HTMLConfig::Language(SOYCMS_LANGUAGE);

//日本語以外はメッセージファイルを切り替える。user.config.php読み込み後に移動
if(SOYCMS_LANGUAGE !== "ja" && file_exists(dirname(__FILE__) . "/message/language/" . SOYCMS_LANGUAGE)){
	//大豆君用メッセージファイルのディレクトリ
	define("CMS_SOYBOY_MESSAGE_DIR", dirname(__FILE__) . "/message/language/" . SOYCMS_LANGUAGE . "/soyboy");
	//ヘルプ用メッセージファイルディレクトリ
	define("CMS_HELP_MESSAGE_DIR", dirname(__FILE__) . "/message/language/" . SOYCMS_LANGUAGE . "/help");
	//管理画面用メッセージファイルディレクトリ
	define("CMS_CONTROLPANEL_MESSAGE_DIR", dirname(__FILE__) . "/message/language/" . SOYCMS_LANGUAGE . "/soycms");
}else{
	define("CMS_SOYBOY_MESSAGE_DIR", dirname(__FILE__) . "/message/soyboy");
	define("CMS_HELP_MESSAGE_DIR", dirname(__FILE__) . "/message/help");
	define("CMS_CONTROLPANEL_MESSAGE_DIR", dirname(__FILE__) . "/message/soycms");
}

//管理側URLの設定
if(defined("SOYCMS_ADMIN_ROOT")) define("SOY2_DOCUMENT_ROOT", str_replace("\\", "/", SOYCMS_ADMIN_ROOT));

//SOY CMS, SOY Shop
define("SOYCMS_COMMON_DIR", SOY2::RootDir());
define("SOYSHOP_COMMON_DIR", dirname(SOY2::RootDir()) . "/soyshop/webapp/src/");

//さくらの共有サーバのSSL対策
if(!isset($_SERVER["HTTPS"]) && isset($_SERVER["HTTP_X_SAKURA_FORWARDED_FOR"])){
	$_SERVER["HTTPS"] = "on";
	$_SERVER["SERVER_PORT"] = "443";
}

//headerの送信
header("Content-Type: text/html; charset=utf-8");
//header("Content-Language: ".SOYCMS_LANGUAGE);

//fatal error
register_shutdown_function("soycms_shutdown");
function soycms_shutdown(){
	if(function_exists("error_get_last")){// PHP 5.2.0 or later
		$error = error_get_last();
		if(is_array($error) && isset($error["type"])){
			if($error["type"] == E_ERROR || $error["type"] == E_RECOVERABLE_ERROR){
				$exception = new ErrorException($error["message"], 100, $error["type"], $error["file"], $error["line"]);
				include_once(dirname(__FILE__) . "/error/admin.php");
				exit;
			}
		}
	}
}
