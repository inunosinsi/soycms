<?php
class CommonFavoriteItemOrderComplete extends SOYShopOrderComplete{

	function execute(SOYShop_Order $order){
		$itemOrders = soyshop_get_item_orders($order->getId());
		if(!count($itemOrders)) return;

		$favLogic = SOY2Logic::createInstance("module.plugins.common_favorite_item.logic.FavoriteLogic");
		foreach($itemOrders as $itemOrder){
			$favId = $favLogic->getFavoriteItem((int)$itemOrder->getItemId(), $order->getUserId())->getId();
			if(!is_numeric($favId)) continue;

			//IDがあった場合は処理を続ける
			$favLogic->update((int)$itemOrder->getItemId(), $order->getUserId());
		}
	}
}

SOYShopPlugin::extension("soyshop.order.complete", "common_favorite_item", "CommonFavoriteItemOrderComplete");
