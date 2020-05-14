<?php
class CommonFavoriteItemOrderComplete extends SOYShopOrderComplete{

	function execute(SOYShop_Order $order){
		$userId = $order->getUserId();
		$favoriteLogic = SOY2Logic::createInstance("module.plugins.common_favorite_item.logic.FavoriteLogic");

		$orderLogic = SOY2Logic::createInstance("logic.order.OrderLogic");
		$itemOrders = $orderLogic->getItemsByOrderId($order->getId());

		foreach($itemOrders as $itemOrder){
			$itemId = $itemOrder->getItemId();
			$favorite = $favoriteLogic->getFavoriteItem($itemId, $userId);

			//IDがあった場合は処理を続ける
			if(!is_null($favorite->getId())){
				$favoriteLogic->updateFavorite($itemId, $userId);
			}
		}
	}
}

SOYShopPlugin::extension("soyshop.order.complete", "common_favorite_item", "CommonFavoriteItemOrderComplete");
