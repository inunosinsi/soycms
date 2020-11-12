<?php

//Load SOY2 settings
include(dirname(__FILE__) . "/common.conf.php");

//管理画面URLの隠蔽
$commonDir = dirname(dirname(dirname(dirname(__FILE__)))) . "/common/";
if(!strpos($_SERVER["REQUEST_URI"], "index.php") && !defined("SOYSHOP_ADMIN_URI") && file_exists($commonDir . "config/admin.uri.config.php")){
	include($commonDir . "config/admin.uri.config.php");
	if(is_numeric(strpos($_SERVER["REQUEST_URI"], "soyshop")) && SOYSHOP_ADMIN_URI != "soyshop"){
		$redirect = str_replace("/soyshop/", "/" . SOYSHOP_ADMIN_URI . "/", $_SERVER["REQUEST_URI"]);
		header("Location:" . $redirect);
		exit;
	}
}

//CMS名の隠蔽
if(!defined("SOYCMS_CMS_NAME") && file_exists($commonDir . "config/advanced.config.php")){
	include($commonDir . "config/advanced.config.php");
}

unset($commonDir);

//Load functions and utilily classes
SOY2::import("base.func.admin",".php");
SOY2::imports("util.*");

//カートの多言語化
SOY2::import("message.MessageManager");

//APPLICATION_ID
if(!defined("APPLICATION_ID")){
	define("APPLICATION_ID", "shop");
}

//Designate a shop to manage
$session = SOY2ActionSession::getUserSession();
if(isset($_GET["site_id"])){
	$session->setAttribute("soyshop.shop.id", $_GET["site_id"]);
	$url = SOY2PageController::createRelativeLink("", true);

	//https:の画面でログインした場合、SOY Shopの管理画面もhttpsにする
	if(isset($_GET["https"]) && strpos($url, "http://") === 0){
		$url = str_replace("http://", "https://", $url);
	}
	header("Location:" . $url);
	exit;
}

//Check your authority to the shop
$shopId = $session->getAttribute("soyshop.shop.id");
if(!$shopId || !$session->getAuthenticated() || !soyshop_admin_login()){
	SOY2PageController::redirect("../admin/");
}

//Load the database setting
define("SOYSHOP_SITE_CONFIG_FILE",str_replace("\\", "/", dirname(__FILE__) . "/shop/${shopId}.conf.php"));
require(SOYSHOP_SITE_CONFIG_FILE);
soyshop_load_db_config();

//debug switch
define("SOYSHOP_"."DEVELOPING_MODE", true);
define("DEBUG_MODE", false);
define("SOY2HTML_AUTO_GENERATE", false);
if(DEBUG_MODE){
	ini_set("display_errors", "On");
	error_reporting(E_ALL);
	//既に定義されている場合がある
	if(!defined("SOY2HTML_CACHE_FORCE")) define("SOY2HTML_CACHE_FORCE", true);
}

//ルートドメインに設定しているかどうか
$file = @file_get_contents($_SERVER["DOCUMENT_ROOT"]."index.php");
if(isset($file) && preg_match('/\("(.*)\//', $file, $res)){
	$isRoot = ($res[1]==SOYSHOP_ID) ? true : false;
}else{
	$isRoot = false;
}
define("SOYSHOP_IS_ROOT",$isRoot);

//管理画面側
if(!defined("SOYSHOP_ADMIN_PAGE")){
	define("SOYSHOP_ADMIN_PAGE", true);
}

//税金の設定
if(!defined("SOYSHOP_CONSUMPTION_TAX_MODE")){
	SOY2::import("domain.config.SOYShop_ShopConfig");
	$config = SOYShop_ShopConfig::load();
	define("SOYSHOP_CONSUMPTION_TAX_MODE", ($config->getConsumptionTax() == SOYShop_ShopConfig::CONSUMPTION_TAX_MODE_ON));
	define("SOYSHOP_CONSUMPTION_TAX_INCLUSIVE_PRICING_MODE", ($config->getConsumptionTaxInclusivePricing() == SOYShop_ShopConfig::CONSUMPTION_TAX_MODE_ON));
}

//libディレクトリ内のcomposerのautoload
define("COMPOSER_LIB_DIR", SOYSHOP_WEBAPP . "lib/vendor/");

//ダミーのメールアドレス用のドメイン(管理画面用:一応、公開側と分けておく)
if(!defined("DUMMY_MAIL_ADDRESS_DOMAIN")) define("DUMMY_MAIL_ADDRESS_DOMAIN", "dummy.soyshop.net");

//CartLogicの内容の一部をSQLite DBに移行するモード
//define("SOYSHOP_USE_CART_TABLE_MODE", false && extension_loaded("sqlite3") && extension_loaded("pdo_sqlite"));
define("SOYSHOP_USE_CART_TABLE_MODE", false);
if(SOYSHOP_USE_CART_TABLE_MODE) SOY2::import("base.cart.db", ".php");
