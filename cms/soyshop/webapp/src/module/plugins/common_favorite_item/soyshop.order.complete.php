<?php
class CommonFavoriteItemOrderComplete extends SOYShopOrderComplete{

	function execute(SOYShop_Order $order){
		$favLogic = SOY2Logic::createInstance("module.plugins.common_favorite_item.logic.FavoriteLogic");

		$itemOrders = SOY2Logic::createInstance("logic.order.OrderLogic")->getItemsByOrderId($order->getId());
		if(count($itemOrders)){
			foreach($itemOrders as $itemOrder){
				$favId = $favLogic->getFavoriteItem((int)$itemOrder->getItemId(), $order->getUserId())->getId();

				//IDがあった場合は処理を続ける
				if(is_numeric($favId)){
					$favLogic->update((int)$itemOrder->getItemId(), $order->getUserId());
				}
			}
		}
	}
}

SOYShopPlugin::extension("soyshop.order.complete", "common_favorite_item", "CommonFavoriteItemOrderComplete");
