<?php
class DeliveryChargeFreeCart extends SOYShopCartBase{

	function afterOperation(CartLogic $cart){
		include_once(dirname(__FILE__) . "/util.php");
		$notification = DeliveryChargeFreeConfigUtil::getNotification();

		//チェックを動作させない条件
		if(!isset($notification["check"]) || $notification["check"] != 1) return;
		if(!isset($notification["text"]) || strlen($notification["text"]) === 0) return;

		$items = $cart->getItems();
		if(!count($items)) return;

		$totalPrice = 0;
		foreach($items as $item){
			$totalPrice += $item->getTotalPrice();
		}

		$price = DeliveryChargeFreeConfigUtil::getPrice();

		//送料無料の線引きライン
		$itemPrice = (int)$price["item_price"];

		//差額
		$difference = $itemPrice - $totalPrice;
		if($difference > 0){
			$noticeMessage = nl2br($notification["text"]);
			$noticeMessage = str_replace("##DIFFERENCE##", number_format($difference), $noticeMessage);
			$cart->addNoticeMessage("plugin_notice", $noticeMessage);
			$cart->setAttribute("page", "Cart01");
			$cart->save();
		}
	}
}
SOYShopPlugin::extension("soyshop.cart", "delivery_charge_free", "DeliveryChargeFreeCart");
