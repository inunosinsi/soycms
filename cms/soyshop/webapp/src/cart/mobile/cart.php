<?php
/*
 * Created on 2009/07/07
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
$cart = CartLogic::getCart();
$cart->setAttribute("page", null);
$cart->save();

//携帯の端末がDoCoMoだった場合、ここでリダイレクト
if(defined("SOYSHOP_IS_MOBILE")&&SOYSHOP_COOKIE){
	if(defined("SOYSHOP_MOBILE_CARRIER")&&SOYSHOP_MOBILE_CARRIER== "DoCoMo"){
		$session = session_name() . "=" . session_id();
		
		$siteUrl = SOYSHOP_SITE_URL;
		$cartUrl = SOYShop_DataSets::get("config.cart.mobile_cart_url","mb/cart");
		$url = $siteUrl.$cartUrl."?" . $session;
		
    	header("location: " . $url);
		exit;
	}
}

?>
