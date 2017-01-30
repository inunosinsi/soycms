<?php

class DeliverySameDayShippingMail extends SOYShopOrderMail{

	/**
	 * メール本文を取得
	 * @return string
	 */
	function getMailBody(SOYShop_Order $order){
		if($this->isUse()){
			
			$shippingAttr = $order->getAttribute("delivery_same_day_shipping.shipping_date");
			$arrivalAttr = $order->getAttribute("delivery_same_day_shipping.arrival_date");
					
			$res = array();
			
			SOY2::import("module.plugins.delivery_same_day_shipping.util.DeliverySameDayShippingUtil");
			$conf = DeliverySameDayShippingUtil::getConfig();
			$title = (isset($conf["title"])) ? $conf["title"] : "";
			$res[] = MessageManager::get("METHOD_DELIVERY") . "：" . $title;
			$res[] = $shippingAttr["name"] . "：" . $shippingAttr["value"];
			$res[] = $arrivalAttr["name"] . "：" . $arrivalAttr["value"];
			$res[] = "";

			return implode("\n", $res);
		}

		return false;
	}

	function getDisplayOrder(){
		return 200;//delivery系は200番台
	}
}

SOYShopPlugin::extension("soyshop.order.mail.user", "delivery_same_day_shipping", "DeliverySameDayShippingMail");
SOYShopPlugin::extension("soyshop.order.mail.confirm", "delivery_same_day_shipping", "DeliverySameDayShippingMail");
SOYShopPlugin::extension("soyshop.order.mail.admin", "delivery_same_day_shipping", "DeliverySameDayShippingMail");