<?php

class CoineyMail extends SOYShopOrderMail{

	/**
	 * メール本文を取得
	 * @return string
	 */
	function getMailBody(SOYShop_Order $order){
		if($this->isUse()){
			$mail = "支払方法：クレジット決済";

			return $mail;
		}

		return false;
	}

	function getDisplayOrder(){
		return 100;//payment系は100番台
	}
}

SOYShopPlugin::extension("soyshop.order.mail.user",		"payment_coiney", "CoineyMail");
SOYShopPlugin::extension("soyshop.order.mail.confirm",	"payment_coiney", "CoineyMail");
SOYShopPlugin::extension("soyshop.order.mail.admin",	"payment_coiney", "CoineyMail");
