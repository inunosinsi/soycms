<?php

class DeliveryNormalMailModule extends SOYShopOrderMail{

	/**
	 * メール本文を取得
	 * @return string
	 */
	function getMailBody(SOYShop_Order $order){
		if($this->isUse()){
			$res = array();
			
			//配達希望時間帯
			$time = $this->order->getAttribute("delivery_normal.time");
			
			SOY2::import("module.plugins.delivery_normal.util.DeliveryNormalUtil");
			$res[] = MessageManager::get("METHOD_DELIVERY") . "：" . DeliveryNormalUtil::getTitle();
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

SOYShopPlugin::extension("soyshop.order.mail.user","delivery_normal","DeliveryNormalMailModule");
SOYShopPlugin::extension("soyshop.order.mail.confirm","delivery_normal","DeliveryNormalMailModule");
SOYShopPlugin::extension("soyshop.order.mail.admin","delivery_normal","DeliveryNormalMailModule");
