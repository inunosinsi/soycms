<?php
class PaymentCustomMailModule extends SOYShopOrderMail{

	/**
	 * メール本文を取得
	 * @return string
	 */
	function getMailBody(SOYShop_Order $order){
		if($this->isUse()){
			SOY2::import("module.plugins.payment_custom.util.PaymentCustomUtil");
			$cnf = PaymentCustomUtil::getConfig();
			$cnf["mail"] = str_replace("##PRICE##", $cnf["price"], $cnf["mail"]);

			return $cnf["mail"];
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
