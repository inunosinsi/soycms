<?php
class PaymentCustomMailModule extends SOYShopOrderMail{

	/**
	 * メール本文を取得
	 * @return string
	 */
	function getMailBody(SOYShop_Order $order){
		if($this->isUse()){
			if(!class_exists("PaymentCustomCommon")){
				include_once(dirname(__FILE__) . "/common.php");
			}
			$custom = PaymentCustomCommon::getCustomConfig();
			$custom["mail"] = str_replace("##PRICE##", $custom["price"],$custom["mail"]);

			return $custom["mail"];
		}

		return false;
	}

	function getDisplayOrder(){
		return 100;//payment系は100番台
	}
}

SOYShopPlugin::extension("soyshop.order.mail.user","payment_custom","PaymentCustomMailModule");
SOYShopPlugin::extension("soyshop.order.mail.confirm","payment_custom","PaymentCustomMailModule");
SOYShopPlugin::extension("soyshop.order.mail.admin","payment_custom","PaymentCustomMailModule");
