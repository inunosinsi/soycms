<?php

class DeliveryCoolMailModule extends SOYShopOrderMail{

	/**
	 * メール本文を取得
	 * @return string
	 */
	function getMailBody(SOYShop_Order $order){
		if($this->isUse()){
			$res = array();
			
			//配達希望時間帯
			$time = $this->order->getAttribute("delivery_cool.time");
			
			$res[] = "配送方法：宅配便(クール)";
			$res[] = $time["name"] . "：" . ( empty($time["value"]) ? "指定なし" : $time["value"] );
			$res[] = "";


			return implode("\n", $res);
		}

		return false;
	}

	function getDisplayOrder(){
		return 200;//delivery系は200番台
	}
}

SOYShopPlugin::extension("soyshop.order.mail.user", "delivery_cool", "DeliveryCoolMailModule");
SOYShopPlugin::extension("soyshop.order.mail.confirm", "delivery_cool", "DeliveryCoolMailModule");
SOYShopPlugin::extension("soyshop.order.mail.admin", "delivery_cool", "DeliveryCoolMailModule");