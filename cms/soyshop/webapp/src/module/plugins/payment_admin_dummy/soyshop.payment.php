<?php
class PaymentAdminDummyModule extends SOYShopPayment{

	function onSelect(CartLogic $cart){
		
		if(defined("SOYSHOP_ADMIN_PAGE") && SOYSHOP_ADMIN_PAGE){
			$module = new SOYShop_ItemModule();
			$module->setId("payment_admin_dummy");
			$module->setType("payment_module");//typeを指定しておくといいことがある
			$module->setName("手数料");
			$module->setPrice($this->getPrice());
			$module->setIsVisible(false);
			$module->setIsInclude(false);
	
			$cart->addModule($module);
		}
	}

	function getName(){
		if(defined("SOYSHOP_ADMIN_PAGE") && SOYSHOP_ADMIN_PAGE){
			return "支払なし";
		}
	}

	function getDescription(){
		if(defined("SOYSHOP_ADMIN_PAGE") && SOYSHOP_ADMIN_PAGE){
			return "ダミーの支払モジュールです。";
		}
	}
	
	function getPrice(){
		return 0;
	}
}
SOYShopPlugin::extension("soyshop.payment","payment_admin_dummy","PaymentAdminDummyModule");
?>