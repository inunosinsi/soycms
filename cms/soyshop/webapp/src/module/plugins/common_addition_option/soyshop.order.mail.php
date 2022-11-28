<?php
class AdditionOptionMail extends SOYShopOrderMail{

	/**
	 * メール本文を取得
	 * @return string
	 */
	function getMailBody(SOYShop_Order $order){
		$itemOrders = soyshop_get_item_orders($order->getId());
		if(!count($itemOrders)) return "";

		$res = array();
		$res[] = "";
		$res[] = "加算対象商品";
		$res[] = "-----------------------------------------";

		foreach($itemOrders as $item){
			//加算対象商品だった場合
			if($item->getIsAddition() != 1) continue;

			$res[] = $item->getItemName() . ":";

			//加算項目と金額を取得
			$name = soyshop_get_item_attribute_value((int)$item->getItemId(), "addition_option_name", "string");
			$name = (strlen($name)) ? $name : "加算";
			$price = soyshop_get_item_attribute_value((int)$item->getItemId(), "addition_option_price", "int");
			$res[] = $name . "：" . $price  . "円" . "*" . $item->getItemCount() . "個";

			$res[] = "";
		}
		$res[] = "";
		return implode("\n", $res);
	}

	function getDisplayOrder(){
		return 200;//delivery系は200番台
	}
}

SOYShopPlugin::extension("soyshop.order.mail.user","common_addition_option","AdditionOptionMail");
SOYShopPlugin::extension("soyshop.order.mail.confirm","common_addition_option","AdditionOptionMail");
SOYShopPlugin::extension("soyshop.order.mail.admin","common_addition_option","AdditionOptionMail");
