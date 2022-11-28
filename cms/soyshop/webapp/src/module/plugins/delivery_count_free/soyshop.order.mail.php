<?php
class DeliveryCountFreeMailModule extends SOYShopOrderMail{

	/**
	 * メール本文を取得
	 * @return string
	 */
	function getMailBody(SOYShop_Order $order){
		if($this->isUse()){
			
			$res = array();
			
			//配達希望日
			$date = $this->order->getAttribute("delivery_count_free.date");
			//配達希望時間帯
			$time = $this->order->getAttribute("delivery_count_free.time");
			
			$res[] = "配送オプション";
			$res[] = "-----------------------------------------";
			$res[] = $date["name"] . "：" . ( empty($date["value"]) ? "指定なし" : $date["value"] );
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

SOYShopPlugin::extension("soyshop.order.mail.user","delivery_count_free","DeliveryCountFreeMailModule");
SOYShopPlugin::extension("soyshop.order.mail.confirm","delivery_count_free","DeliveryCountFreeMailModule");
SOYShopPlugin::extension("soyshop.order.mail.admin","delivery_count_free","DeliveryCountFreeMailModule");
