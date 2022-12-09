<?php
/*
 * Created on 2009/07/07
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

include_once(dirname(__FILE__) . "/classes.php");
SOY2HTMLConfig::PageDir(dirname(__FILE__) . "/pages/");
SOY2::import("domain.config.SOYShop_ShopConfig");
SOY2::import("domain.config.SOYShop_Area");

/* カートのテンプレートの設定 */
$templateDir = SOYSHOP_SITE_DIRECTORY . ".template/cart/" . SOYSHOP_CURRENT_CART_ID . "/";
define("SOYSHOP_DEFAULT_CART_TEMPLATE_DIR",soy2_realpath(dirname(dirname(__FILE__))."/".SOYSHOP_CURRENT_CART_ID) . "pages/");
if(file_exists($templateDir)){
	define("SOYSHOP_MAIN_CART_TEMPLATE_DIR", $templateDir);
}else{
	define("SOYSHOP_MAIN_CART_TEMPLATE_DIR", SOYSHOP_DEFAULT_CART_TEMPLATE_DIR);
}

/* 各種読み込み */
$cart = CartLogic::getCart(SOYSHOP_CURRENT_CART_ID);
$config = SOYShop_ShopConfig::load();
$session = SOY2ActionSession::getUserSession();

//PHPSESSIDの値の更新　スマホでは更新すべきではないらしい PCでもうまくいかないことがあったので廃止
//if(USE_SESSION_REGENERATE_ID_MODE) SOY2ActionSession::regenerateSessionId();

/* 表示するページ */
//進捗の時間切れ判定
$timeLimit = $config->getCartPageTimeLimit() * 60;//秒に変換（同時に数値に変換される）
if($timeLimit > 0 && $cart->getAttribute("last_access") + $timeLimit < SOY2_NOW){
	//期限切れ
	//進み具合をリセット
	$cart->clearAttribute("page");
	//何かがPOSTされている可能性があるのでその値もクリアする
	$_POST = array();
}

//メンテナンスモード
if($config->getIsShowOnlyAdministrator() && !$session->getAuthenticated()){
	$cart->clearAttribute("page");
	$pageId = "Maintenance";
}

$pageId = $cart->getAttribute("page");
if(is_null($pageId)) $pageId = "Cart01";


//税金の表記があるか？を調べる
define("SOYSHOP_CART_IS_TAX_MODULE", $cart->checkTaxModule());


//Pluginの読み込み
SOYShopPlugin::load("soyshop.order.*");



//アクセス時刻を更新
$cart->setAttribute("last_access", SOY2_NOW);

/* 表示 */
try{
	$pageName = ($cart->checkBanIpAddress()) ? "Ban" : $pageId;
	$page = SOY2HTMLFactory::createInstance($pageName . "Page");
	$page->buildModules();
	$page->display();
}catch(Exception $e){
	//管理者にカートでエラーが表示された旨を伝える
	$cart->sendNoticeCartErrorMail($e);

	$cart->clearAttribute("page");
	$page = SOY2HTMLFactory::createInstance("ErrorPage");
	$page->display();
}
