<?php
SOY2DAOFactory::importEntity("SOYShop_DataSets");
include(dirname(__FILE__) . "/common.php");
class CustomPaymentModule extends SOYShopPayment{

	function onSelect(CartLogic $cart){
		
		$custom = PaymentCustomCommon::getCustomConfig();

		if(isset($custom["price"]) && $custom["price"]> 0){
			$price = $custom["price"];
			$isVisible = true;
		}else{
			$price = 0;
			$isVisible = false;
		}

		$module = new SOYShop_ItemModule();
		$module->setId("payment_custom");
		$module->setType("payment_module");//typeを指定しておくといいことがある
		$module->setName("手数料");
		$module->setPrice($price);
		$module->setIsVisible($isVisible);

		$cart->addModule($module);

		//属性の登録
		$cart->setOrderAttribute("payment_custom","支払方法",$custom["name"]);
	}

	function getName(){
		$custom = PaymentCustomCommon::getCustomConfig();
		return $custom["name"];
	}

	function getDescription(){
		$custom = PaymentCustomCommon::getCustomConfig();
		$custom["description"] = str_replace("##PRICE##", $custom["price"],$custom["description"]);
		return nl2br($custom["description"]);
	}
	
	function getPrice(){
		$custom = PaymentCustomCommon::getCustomConfig();
		if(isset($custom["price"]) && $custom["price"]> 0)return $custom["price"];
	}
}
SOYShopPlugin::extension("soyshop.payment","payment_custom","CustomPaymentModule");
?>