<?php

class SalePeriodCart extends SOYShopCartBase{

	const PLUGIN_ID = "common_sale_period";

	function afterOperation(CartLogic $cart){
		//カートに入っている商品を削除する時は実行しない
		if(isset($_GET["a"]) && $_GET["a"] == "remove") return;

		$itemOrders = $cart->getItems();
		if(!count($itemOrders)) return;

		$last = end(array_keys($itemOrders));
		//今回カートに入れた商品

		$item = soyshop_get_item_object($itemOrders[$last]->getItemId());
		$price = self::_logic()->getDisplayPrice($item);

		$itemOrders[$last]->setItemPrice($price);
		$itemOrders[$last]->setTotalPrice($price * (int)$itemOrders[$last]->getItemCount());
	}

	private function _logic(){
		static $l;
		if(is_null($l)) $l = SOY2Logic::createInstance("module.plugins.". self::PLUGIN_ID . ".logic.PriceLogic");
		return $l;
	}
}
SOYShopPlugin::extension("soyshop.cart", "common_sale_period", "SalePeriodCart");
