<?php

class AmazonPayOrderMail extends SOYShopOrderMail{

	/**
	 * メール本文を取得
	 * @return string
	 */
	function getMailBody(SOYShop_Order $order){
		if($this->isUse()){
			$mail = "支払方法：Amazon Pay";

			return $mail;
		}

		return false;
	}

	function getDisplayOrder(){
		return 100;//payment系は100番台
	}
}

SOYShopPlugin::extension("soyshop.order.mail.user", 	"payment_amazon_pay", "AmazonPayOrderMail");
SOYShopPlugin::extension("soyshop.order.mail.confirm", 	"payment_amazon_pay", "AmazonPayOrderMail");
SOYShopPlugin::extension("soyshop.order.mail.admin", 	"payment_amazon_pay", "AmazonPayOrderMail");
