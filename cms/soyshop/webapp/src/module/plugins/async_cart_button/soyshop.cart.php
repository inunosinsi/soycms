<?php

class AsyncCartButtonCart extends SOYShopCartBase{

	function doOperation(){
		
		if(isset($_REQUEST["mode"]) && $_REQUEST["mode"] == "async" && isset($_REQUEST["item"])){
			//在庫チェック
			$itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
			
			try{
				$children = $itemDao->getByType($_REQUEST["item"]);
			}catch(Exception $e){
				$children = array();
			}
			
			//小商品がある場合は調べない
			if(!count($children)){
				try{
					$obj = $itemDao->getById($_REQUEST["item"]);
				}catch(Exception $e){
					header("HTTP/1.1 204 No Content");
					exit;
				}
				
				//カートに入っている商品数も加味する
				$cart = CartLogic::getCart();
				$inCnt = 0;
				$items = $cart->getItems();
				if(count($items)) foreach($items as $item){
					if((int)$item->getItemId() === (int)$_REQUEST["item"]){
						$inCnt += (int)$item->getItemCount();
					}
				}
					
				$cnt = (isset($_GET["count"]) && is_numeric($_GET["count"]) && (int)$_GET["count"] > 0) ? (int)$_GET["count"] : 1;
					
				//非同期カートプラグインで在庫数が0の場合は別のステータスコードを返す
				if($cnt > ((int)$obj->getStock() - $inCnt)){
					header("HTTP/1.1 204 No Content");
					exit;
				}
			}
		}
	}
}
SOYShopPlugin::extension("soyshop.cart", "async_cart_button", "AsyncCartButtonCart");
?>