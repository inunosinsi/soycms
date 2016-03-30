<?php

class PaymentDaibikiMailModule extends SOYShopOrderMail{

	/**
	 * メール本文を取得
	 * @return string
	 */
	function getMailBody(SOYShop_Order $order){
		if($this->isUse()){
			$mail = SOYShop_DataSets::get("payment_daibiki.mail","支払方法：代金引換");

			//料金を置換
			//$mail = str_replace("#ACCOUNT#",@$array["account"], $mail);

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
