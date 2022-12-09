<?php

class AsyncCartButtonCart extends SOYShopCartBase{

	function doOperation(){

		if(isset($_REQUEST["mode"]) && $_REQUEST["mode"] == "async" && isset($_REQUEST["item"])){
			//在庫チェック
			$itemId = (is_numeric($_REQUEST["item"])) ? (int)$_REQUEST["item"] : 0;
			$children = soyshop_get_item_children($itemId);

			//子商品がある場合は調べない
			if(!count($children)){
				$item = soyshop_get_item_object($itemId);
				if(!is_numeric($item->getId())){
					header("HTTP/1.1 204 No Content");
					exit;
				}
				
				//カートに入っている商品数も加味する
				$cart = CartLogic::getCart();
				$inCnt = 0;
				$itemOrders = $cart->getItems();
				if(count($itemOrders)){
					foreach($itemOrders as $itemOrder){
						if((int)$itemOrder->getItemId() === $itemId){
							$inCnt += (int)$itemOrder->getItemCount();
						}
					}
				}

				$cnt = (isset($_GET["count"]) && is_numeric($_GET["count"]) && (int)$_GET["count"] > 0) ? (int)$_GET["count"] : 1;

				//非同期カートプラグインで在庫数が0の場合は別のステータスコードを返す
				if($cnt > ((int)$item->getStock() - $inCnt)){
					header("HTTP/1.1 204 No Content");
					exit;
				}
			}
		}
	}
}
SOYShopPlugin::extension("soyshop.cart", "async_cart_button", "AsyncCartButtonCart");
