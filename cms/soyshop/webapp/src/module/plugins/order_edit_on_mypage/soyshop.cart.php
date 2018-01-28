<?php

class OrderEditOnMypageCart extends SOYShopCartBase{

	function doOperation(){
		$mypage = MyPageLogic::getMyPage();
		if($mypage->getIsLoggedin()){	//ログインを確認しておく
			$isEditMode = $mypage->getAttribute("order_edit_on_mypage");
			if($isEditMode){
				//マイページのセッションに入っている商品を取り出す
				if(isset($_REQUEST["a"]) && $_REQUEST["a"] == "add"){
					//@ToDo ItemOptionをどうにかしなきゃ
					$itemOrders = $mypage->getAttribute("order_edit_item_orders");
					$orderId = null;
					foreach($itemOrders as $itemOrder){
						$orderId = $itemOrder->getOrderId();
						break;
					}

					$item = SOY2DAOFactory::create("shop.SOYShop_ItemDAO")->getById($_REQUEST["item"]);
					var_dump($item);

					SOY2::import("domain.order.SOYShop_ItemOrder");
					$itemOrder = new SOYShop_ItemOrder();
					$itemOrder->setOrderId($orderId);
					$itemOrder->setItemId($item->getId());
					$itemOrder->setItemCount($_REQUEST["count"]);
					$itemOrder->setItemPrice($item->getPrice());
					$itemOrder->setTotalPrice($itemOrder->getItemCount() * $itemOrder->getItemPrice());
					$itemOrder->setItemName($item->getName());

					$itemOrders[] = $itemOrder;
					$mypage->setAttribute("order_edit_item_orders", $itemOrders);
					$mypage->clearAttribute("order_edit_on_mypage");
					$mypage->setAttribute("order_edit_is_edit", true);
					$mypage->save();

					//マイページへ戻る
					header("Location:" . soyshop_get_mypage_url() . "/order/edit/item/" . $orderId);
					exit;

				}
			}
		}
	}
}
SOYShopPlugin::extension("soyshop.cart", "order_edit_on_mypage", "OrderEditOnMypageCart");
