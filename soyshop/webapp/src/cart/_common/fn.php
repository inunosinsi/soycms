<?php

function soyshop_cart_register_payment_module(CartLogic $cart, string $moduleId){
	$cart->setAttribute("payment_module", $moduleId);
	SOYShopPlugin::load("soyshop.payment", soyshop_get_plugin_object($moduleId));
	$delegate = SOYShopPlugin::invoke("soyshop.payment", array(
		"mode" => "select",
		"cart" => $cart
	));

	//Cart05が必要かどうか引き継がれない時は再度調べる
	if(is_null($cart->getAttribute("has_option"))){
		$cart->setAttribute("has_option", $delegate->getHasOption());
	}
}

function soyshop_cart_register_delivery_module(CartLogic $cart, string $moduleId){
	$cart->setAttribute("delivery_module", $moduleId);
	SOYShopPlugin::load("soyshop.delivery", soyshop_get_plugin_object($moduleId));
	SOYShopPlugin::invoke("soyshop.delivery", array(
		"mode" => "select",
		"cart" => $cart
	));
}
