<?php
/*
 * Created on 2009/07/07
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

include_once(dirname(__FILE__) . "/classes.php");
SOY2HTMLConfig::PageDir(dirname(__FILE__).  "/pages/");
SOY2::import("domain.config.SOYShop_ShopConfig");
SOY2::import("domain.config.SOYShop_Area");


//カートのテンプレートの設定
$templateDir = SOYSHOP_SITE_DIRECTORY . ".template/cart/" . SOYSHOP_CURRENT_CART_ID . "/";
define("SOYSHOP_DEFAULT_CART_TEMPLATE_DIR",soy2_realpath(dirname(__FILE__)) . "pages/");
if(file_exists($templateDir)){
	define("SOYSHOP_MOBILE_CART_TEMPLATE_DIR",$templateDir);
}else{
	define("SOYSHOP_MOBILE_CART_TEMPLATE_DIR",SOYSHOP_DEFAULT_CART_TEMPLATE_DIR);
}

//カートモジュールの設定
$cart = CartLogic::getCart(SOYSHOP_CURRENT_CART_ID);
$pageId = $cart->getAttribute("page");
if(is_null($pageId))$pageId = "Cart01";

//税金の表記があるか？を調べる
define("SOYSHOP_CART_IS_TAX_MODULE", $cart->checkTaxModule());

//Pluginの読み込み
SOYShopPlugin::load("soyshop.order.*");

try{
	$page = SOY2HTMLFactory::createInstance($pageId . "Page");
	$page->display();
}catch(Exception $e){

	$cart->setAttribute("page", null);

	$page = SOY2HTMLFactory::createInstance("ErrorPage");
	$page->display();
}

?>