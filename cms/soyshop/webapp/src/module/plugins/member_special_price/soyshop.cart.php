<?php

class MemberSpecialPriceCart extends SOYShopCartBase{

	/**
	 * @param CartLogic
	 */
	function doPost02(CartLogic $cart){
		// 値引対象がユーザーカスタムフィールドの場合
		SOY2::import("module.plugins.member_special_price.util.MemberSpecialPriceUtil");
		$cfgs = MemberSpecialPriceUtil::getConfig();

		if(count($cfgs)){
			$itemOrders = $cart->getItems();
			if(count($itemOrders)){
				$isChange = false;
			
				foreach($cfgs as $cfg){
					// attributeの値が3以下の場合は顧客属性に設定している
					if(!isset($cfg["attribute"]) || $cfg["attribute"] <= 3) continue;

					foreach($itemOrders as $idx => $itemOrder){
						$item = soyshop_get_item_object((int)$itemOrder->getItemId());
						$price = self::_logic()->getSpecialPrice($item);

						// カート内で割引対象外になった場合
						if(is_null($price)){
							if($item->getSellingPrice() != $itemOrder->getItemPrice()){
								$itemOrder->setItemPrice($item->getSellingPrice());
								$itemOrder->setTotalPrice($itemOrder->getItemPrice() * $itemOrder->getItemCount());
								$itemOrders[$idx] = $itemOrder;
								$isChange = true;
							}
						
						// カート内で割引対象内になった場合
						} else if(is_numeric($price)){
							if($itemOrder->getItemPrice() != $price){
								$itemOrder->setItemPrice($price);
								$itemOrder->setTotalPrice($itemOrder->getItemPrice() * $itemOrder->getItemCount());
								$itemOrders[$idx] = $itemOrder;
								$isChange = true;
							}
						}
					}
				}

				if($isChange){
					$cart->setItems($itemOrders);
					$cart->save();
				}
			}
		}
	}

	private function _logic(){
		static $l;
		if(is_null($l)) $l = SOY2Logic::createInstance("module.plugins.member_special_price.logic.SpecialPriceLogic");
		return $l;
	}
}
SOYShopPlugin::extension("soyshop.cart", "member_special_price", "MemberSpecialPriceCart");
