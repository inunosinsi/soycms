<?php
include_once(dirname(__FILE__) . "/common.php");

class YuchoPaymentMailModule extends SOYShopOrderMail{

	/**
	 * メール本文を取得
	 * @return string
	 */
	function getMailBody(SOYShop_Order $order){
		if($this->isUse()){
			$array = PaymentYuchoCommon::getConfigText();
			$res = (isset($array["mail"])) ? $array["mail"] : "";

			//replace
			if(isset($array["account"])){
				$res = str_replace("#ACCOUNT#",$array["account"], $res);
			}

			return $res;
		}

		return false;
	}

	function getDisplayOrder(){
		return 100;//payment系は100番台
	}

}

class YuchoPaymentAdminMailModule extends SOYShopOrderMail{

	/**
	 * メール本文を取得
	 * @return string
	 */
	function getMailBody(SOYShop_Order $order){
		if($this->isUse()){
			return "支払方法：ゆうちょ銀行\n";
		}

		return false;
	}

}

SOYShopPlugin::extension("soyshop.order.mail.user","payment_yucho","YuchoPaymentMailModule");
SOYShopPlugin::extension("soyshop.order.mail.confirm","payment_yucho","YuchoPaymentMailModule");
SOYShopPlugin::extension("soyshop.order.mail.admin","payment_yucho","YuchoPaymentAdminMailModule");
