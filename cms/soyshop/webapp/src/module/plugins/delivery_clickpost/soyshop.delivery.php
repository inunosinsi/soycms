<?php

class DeliveryClickPostModule extends SOYShopDelivery{

	function __construct(){
		SOY2::import("module.plugins.delivery_clickpost.util.ClickPostUtil");
	}

	function onSelect(CartLogic $cart){
		$module = new SOYShop_ItemModule();
		$module->setId("delivery_clickpost");
		$module->setName("郵送");
		$module->setType("delivery_module");	//typeを指定しておくといいことがある
		$module->setPrice($this->getPrice());
		$cart->addModule($module);

		//属性の登録
		$cart->setOrderAttribute("delivery_clickpost", MessageManager::get("METHOD_DELIVERY"), $this->getName());
	}

	function getName(){
		$cfg = ClickPostUtil::getConfig();
		return (isset($cfg["name"])) ? $cfg["name"] : "郵送";
	}

	function getDescription(){
		SOY2::import("module.plugins.delivery_clickpost.cart.DeliveryClickPostCartPage");
		$form = SOY2HTMLFactory::createInstance("DeliveryClickPostCartPage");
		$form->setCart($this->getCart());
		$form->execute();
		return $form->getObject();
	}

	function getPrice(){
		$cart = $this->getCart();
			
		$cfg = ClickPostUtil::getConfig();

		// 購入金額による送料無料設定
		if(isset($cfg["shippingFree"]) && is_numeric($cfg["shippingFree"])){
			if($cart->getItemPrice() >= $cfg["shippingFree"]) return 0;
		}

		$fee = (isset($cfg["fee"]) && is_numeric($cfg["fee"])) ? (int)$cfg["fee"] : 185;

		// 注文個数に応じて送料の個口を変更
		$itemOrders = $cart->getItems();
		$itemCount = 0;
		if(count($itemOrders)){
			foreach($itemOrders as $itemOrder){
				$itemCount += (int)$itemOrder->getItemCount();
			}
		}

		$pack = 1;
		if($itemCount > 1 && isset($cfg["feePerPack"]) && is_array($cfg["feePerPack"]) && count($cfg["feePerPack"])){
			foreach($cfg["feePerPack"] as $feeCfg){
				if($itemCount >= (int)$feeCfg["pack"]){
					$pack = (int)$feeCfg["fee"];
				}
			}
		}
	
		return $fee*$pack;
	}
}
SOYShopPlugin::extension("soyshop.delivery", "delivery_clickpost", "DeliveryClickPostModule");
