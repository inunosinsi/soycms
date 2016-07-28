<?php

class ItemStandardCart extends SOYShopCartBase{

	function doOperation(){
		//Standardが無ければ通常のdoOperation
		if(isset($_POST["Standard"])){
			$child = SOY2Logic::createInstance("module.plugins.item_standard.logic.ChildItemLogic")->getChildItem($_REQUEST["item"], $_POST["Standard"]);
			if(!is_null($child->getId())) {
				SOY2::import("util.SOYShopPluginUtil");
				if(SOYShopPluginUtil::checkIsActive("async_cart_button")){
					
					//カートに入っている商品数も加味する
					$cart = CartLogic::getCart();
					$inCnt = 0;
					$items = $cart->getItems();
					if(count($items)) foreach($items as $item){
						if((int)$item->getItemId() === (int)$child->getId()){
							$inCnt += (int)$item->getItemCount();
						}
					}
				
					$cnt = (isset($_GET["count"]) && is_numeric($_GET["count"]) && (int)$_GET["count"] > 0) ? (int)$_GET["count"] : 1;
				
					//非同期カートプラグインで在庫数が0の場合は別のステータスコードを返す
					if($cnt > ((int)$child->getStock() - $inCnt)){
						header("HTTP/1.1 204 No Content");
						exit;
					}
				}
					
				$_REQUEST["item"] = $child->getId();
			}
		}
	}
}
SOYShopPlugin::extension("soyshop.cart", "item_standard", "ItemStandardCart");
?>