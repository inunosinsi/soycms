<?php
class CustomPaymentModule extends SOYShopPayment{

	function __construct(){
		SOY2::import("module.plugins.payment_custom.util.PaymentCustomUtil");
	}

	function onSelect(CartLogic $cart){

		$price = self::getPrice();
		$isVisible = ($price > 0);

		$module = new SOYShop_ItemModule();
		$module->setId("payment_custom");
		$module->setType("payment_module");//typeを指定しておくといいことがある
		$module->setName("手数料");
		$module->setPrice($price);
		$module->setIsVisible($isVisible);

		$cart->addModule($module);

		//属性の登録
		$cnf = PaymentCustomUtil::getConfig();
		$cart->setOrderAttribute("payment_custom","支払方法", $cnf["name"]);
	}

	function getName(){
		$cnf = PaymentCustomUtil::getConfig();
		return $cnf["name"];
	}

	function getDescription(){
		$cnf = PaymentCustomUtil::getConfig();
		$cnf["description"] = str_replace("##PRICE##", $cnf["price"], $cnf["description"]);
		return nl2br($cnf["description"]);
	}

	function getPrice(){
		$cnf = PaymentCustomUtil::getConfig();
		return (isset($cnf["price"]) && is_numeric($cnf["price"])) ? $cnf["price"] : 0;
	}
}
SOYShopPlugin::extension("soyshop.payment","payment_custom","CustomPaymentModule");
