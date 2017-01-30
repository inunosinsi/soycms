<?php

class DeliveryAdminDummyModule extends SOYShopDelivery{
	
	function onSelect(CartLogic $cart){
		
		if(defined("SOYSHOP_ADMIN_PAGE") && SOYSHOP_ADMIN_PAGE){
			//割引を先に行う
			$module = new SOYShop_ItemModule();
			$module->setId("delivery_admin_dummy");
			$module->setName("送料");
			$module->setType("delivery_module");	//typeを指定しておくといいことがある
			$module->setPrice($this->getPrice());
			$module->setIsVisible(false);
			$module->setIsInclude(false);
			
			$cart->addModule($module);
		}
	}

	function getName(){
		if(defined("SOYSHOP_ADMIN_PAGE") && SOYSHOP_ADMIN_PAGE){
			return "配送なし";
		}
	}

	function getDescription(){
		if(defined("SOYSHOP_ADMIN_PAGE") && SOYSHOP_ADMIN_PAGE){
			return "ダミーの配送モジュールです。";
		}
	}
	
	function getPrice(){
		return 0;
	}
}
SOYShopPlugin::extension("soyshop.delivery", "delivery_admin_dummy", "DeliveryAdminDummyModule");
?>