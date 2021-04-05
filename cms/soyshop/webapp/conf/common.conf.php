<?php
//さくらの共有サーバのSSL対策
if(isset($_SERVER["HTTP_X_SAKURA_FORWARDED_FOR"])){
	$_SERVER["HTTPS"] = "on";
	$_SERVER["SERVER_PORT"] = "443";
}

//session → If you do not load it here, set it in php.config.php.
if(file_exists(dirname(__FILE__) . "/session.conf.php")) include_once("session.conf.php");

/**
 * commonとあるが管理画面専用
 * 主にSOY2関連の設定
 */

//define
define("SOYSHOP_ROOT", str_replace("\\", "/", dirname(dirname(dirname(__FILE__)))) . "/");
define("SOYSHOP_WEBAPP", SOYSHOP_ROOT . "webapp/");
define("SOY2_NOW", time());	//現在時刻
define("SOYSHOP_BUILD_TIME",1434600689);//ビルド日時：ビルド時に置換される
define("SOYSHOP_VERSION", trim(file_get_contents(SOYSHOP_ROOT . "VERSION")));

//SOY CMSのphp.config.phpを読み込む
if(file_exists(dirname(SOYSHOP_ROOT) . "/common/config/php.config.php")){
	include_once(dirname(SOYSHOP_ROOT) . "/common/config/php.config.php");
}else{
	mb_language('Japanese');
	mb_internal_encoding('UTF-8');
	mb_regex_encoding(mb_internal_encoding());
}

//include SOY2
if(!class_exists("SOY2")) include_once(SOYSHOP_WEBAPP . "lib/soy2_build.php");
include_once(SOYSHOP_WEBAPP . "lib/magic_quote_gpc.php");

//configure SOY2
SOY2::RootDir(SOYSHOP_WEBAPP . "src/");

//SOY2PageController
SOY2::import("base.SOYShopPageController");
SOY2PageController::init("SOYShopPageController");

//SOY2HTML
SOY2HTMLConfig::CacheDir(SOYSHOP_ROOT . "cache/");
if(SOYSHOP_VERSION != "SOYSHOP_VERSION") SOY2HTMLConfig::setOption("cache_prefix", SOYSHOP_VERSION . "_");
SOY2HTMLConfig::PageDir(SOYSHOP_WEBAPP . "pages/");
SOY2HTMLPlugin::addPlugin("page", "PagePlugin");
SOY2HTMLPlugin::addPlugin("link", "LinkPlugin");
SOY2HTMLPlugin::addPlugin("src", "SrcPlugin");
SOY2HTMLPlugin::addPlugin("display", "DisplayPlugin");
SOY2HTMLPlugin::addPlugin("panel", "PanelPlugin");
SOY2HTMLPlugin::addPlugin("ignore", "IgnorePlugin");

DisplayPlugin::toggle("updated", (isset($_GET["updated"])));
DisplayPlugin::toggle("deleted", (isset($_GET["deleted"])));
DisplayPlugin::toggle("failed", (isset($_GET["failed"])));

//SOY2DAO
SOY2DAOConfig::DaoDir(SOYSHOP_WEBAPP . "src/domain/");
SOY2DAOConfig::EntityDir(SOYSHOP_WEBAPP . "src/domain/");
SOY2DAOConfig::DaoCacheDir(SOYSHOP_ROOT . "cache/");

SOY2DAOConfig::setOption("connection_failure", "throw");
if(SOYSHOP_VERSION != "SOYSHOP_VERSION") SOY2DAOConfig::setOption("cache_prefix", SOYSHOP_VERSION . "_");

//etc
SOY2::import("message.MessageManager");
SOY2::import("domain.config.SOYShop_DataSets");
SOY2::import("logic.plugin.SOYShopPlugin");


//SOY CMSのuser.config.phpを読み込む
if(file_exists(dirname(SOYSHOP_ROOT) . "/common/config/user.config.php")){
	include_once(dirname(SOYSHOP_ROOT) . "/common/config/user.config.php");
	if(defined("SOYCMS_TARGET_DIRECTORY")){
		$targetDir = (strrpos(SOYCMS_TARGET_DIRECTORY, "/") === 0) ? substr(SOYCMS_TARGET_DIRECTORY, 0 , strlen(SOYCMS_TARGET_DIRECTORY) - 1) : SOYCMS_TARGET_DIRECTORY;
		define("SOY2_DOCUMENT_ROOT", $targetDir);
	}
}
//define URL
define("SOYSHOP_ADMIN_URL", SOY2PageController::createRelativeLink("index.php"));
define("SOYSHOP_BASE_URL", SOY2PageController::createRelativeLink("", true));
define("SOYCMS_ADMIN_URL", SOY2PageController::createRelativeLink("../admin/"));
if(!defined("SOYCMS_PHP_CGI_MODE")) define("SOYCMS_PHP_CGI_MODE", function_exists("php_sapi_name") && stripos(php_sapi_name(), "cgi") !== false );

//include
SOY2::import("base.define", ".php");
SOY2::import("base.func.common", ".php");

//document rootの末尾は/で終わるのを期待
if(function_exists("soy2_realpath")){
	$_SERVER["DOCUMENT_ROOT"] = soy2_realpath($_SERVER["DOCUMENT_ROOT"]);
}

//fatal error
register_shutdown_function("soyshop_shutdown");
function soyshop_shutdown(){
	if(function_exists("error_get_last")){// PHP 5.2.0 or later
		$error = error_get_last();
		if(is_array($error) && isset($error["type"])){
			if($error["type"] == E_ERROR || $error["type"] == E_RECOVERABLE_ERROR){
				$html = var_export($error, true);
				include_once(SOYSHOP_WEBAPP . "src/" . "layout/error.php");
				exit;
			}
		}
	}
}

//
function soyshop_load_db_config(){
	if(!defined("SOYSHOP_SITE_DSN")){
		define("SOYSHOP_SITE_DSN", "sqlite:" . SOYSHOP_SITE_DIRECTORY . ".db/sqlite.db");
	}
	SOY2DAOConfig::Dsn(SOYSHOP_SITE_DSN);
	if(defined("SOYSHOP_SITE_USER")) SOY2DAOConfig::user(SOYSHOP_SITE_USER);
	if(defined("SOYSHOP_SITE_PASS")) SOY2DAOConfig::pass(SOYSHOP_SITE_PASS);
	if(!defined("SOYSHOP_DB_TYPE")){
		define("SOYSHOP_DB_TYPE", SOY2DAOConfig::type());
	}
}
