<?php

class DeliveryClickPostMailModule extends SOYShopOrderMail{

	/**
	 * メール本文を取得
	 * @return string
	 */
	function getMailBody(SOYShop_Order $order){
		if($this->isUse()){
			$res = array();
			
			$res[] = MessageManager::get("METHOD_DELIVERY") . "：郵送";

			return implode("\n", $res);
		}

		return false;
	}

	function getDisplayOrder(){
		return 200;//delivery系は200番台
	}
}

SOYShopPlugin::extension("soyshop.order.mail.user","delivery_clickpost","DeliveryClickPostMailModule");
SOYShopPlugin::extension("soyshop.order.mail.confirm","delivery_clickpost","DeliveryClickPostMailModule");
SOYShopPlugin::extension("soyshop.order.mail.admin","delivery_clickpost","DeliveryClickPostMailModule");
