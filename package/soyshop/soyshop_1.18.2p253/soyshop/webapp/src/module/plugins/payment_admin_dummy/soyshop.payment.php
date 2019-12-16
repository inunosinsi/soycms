<?php
class PaymentAdminDummyModule extends SOYShopPayment{

	function onSelect(CartLogic $cart){

		$module = new SOYShop_ItemModule();
		$module->setId("payment_admin_dummy");
		$module->setType("payment_module");//typeを指定しておくといいことがある
		$module->setName("決済手数料");
		$module->setPrice($this->getPrice());
		$module->setIsVisible(false);
		$module->setIsInclude(false);

		$cart->addModule($module);

	}

	function getName(){
		return "支払い";
	}

	function getDescription(){
			return "選ぶべき支払い方法がない場合はこれを選択してください。";
	}

	function getPrice(){
		return 0;
	}
}
if(defined("SOYSHOP_ADMIN_PAGE") && SOYSHOP_ADMIN_PAGE){
	SOYShopPlugin::extension("soyshop.payment","payment_admin_dummy","PaymentAdminDummyModule");
}
