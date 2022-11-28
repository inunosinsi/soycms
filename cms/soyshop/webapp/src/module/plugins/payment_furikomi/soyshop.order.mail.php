<?php

class FurikomiPaymentMailModule extends SOYShopOrderMail{

	/**
	 * メール本文を取得
	 * @return string
	 */
	function getMailBody(SOYShop_Order $order){
		if($this->isUse()){
			SOY2::import("module.plugins.payment_furikomi.util.PaymentFurikomiUtil");
			$array = PaymentFurikomiUtil::getConfigText();
			$res = (isset($array["mail"])) ? $array["mail"] : "";

			//replace
			if(isset($array["account"])){
				$res = str_replace("#ACCOUNT#", $array["account"], $res);
			}

			return $res;
		}

		return false;
	}

	function getDisplayOrder(){
		return 100;//payment系は100番台
	}

}

class FurikomiPaymentAdminMailModule extends SOYShopOrderMail{

	/**
	 * メール本文を取得
	 * @return string
	 */
	function getMailBody(SOYShop_Order $order){
		if($this->isUse()){
			return "支払方法：銀行振り込み\n";
		}

		return false;
	}

}

SOYShopPlugin::extension("soyshop.order.mail.user", "payment_furikomi", "FurikomiPaymentMailModule");
SOYShopPlugin::extension("soyshop.order.mail.confirm", "payment_furikomi", "FurikomiPaymentMailModule");
SOYShopPlugin::extension("soyshop.order.mail.admin", "payment_furikomi", "FurikomiPaymentAdminMailModule");
