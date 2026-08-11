<?php

class PaymentDaibikiMailModule extends SOYShopOrderMail{

	/**
	 * メール本文を取得
	 * @return string
	 */
	function getMailBody(SOYShop_Order $order){
		if($this->isUse()){
			SOY2::import("module.plugins.payment_daibiki.util.PaymentDaibikiUtil");
			$mail = PaymentDaibikiUtil::getMailConfig();

			return $mail;
		}

		return false;
	}

	function getDisplayOrder(){
		return 100;//payment系は100番台
	}

}

SOYShopPlugin::extension("soyshop.order.mail.user","payment_daibiki","PaymentDaibikiMailModule");
SOYShopPlugin::extension("soyshop.order.mail.confirm","payment_daibiki","PaymentDaibikiMailModule");
SOYShopPlugin::extension("soyshop.order.mail.admin","payment_daibiki","PaymentDaibikiMailModule");
