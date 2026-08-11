<?php

class SlipNumberMail extends SOYShopOrderMail{

	/**
	 * メール本文を取得
	 * @return string
	 */
	function getMailBody(SOYShop_Order $order){
		$slipNumberChain = SOY2Logic::createInstance("module.plugins.slip_number.logic.SlipNumberLogic")->getSlipNumberByOrderId($order->getId());
		if(strlen($slipNumberChain)){
			$cnf = SlipNumberUtil::getConfig();

			$content = (isset($cnf["content"])) ? $cnf["content"] : "";
			return str_replace("#SLIP_NUMBER#", $slipNumberChain, $content);
		}
	}


	function getDisplayOrder(){
		return 100;//delivery系は200番台
	}
}

SOYShopPlugin::extension("soyshop.order.mail.user", "slip_number", "SlipNumberMail");
SOYShopPlugin::extension("soyshop.order.mail.confirm", "slip_number", "SlipNumberMail");
SOYShopPlugin::extension("soyshop.order.mail.admin", "slip_number", "SlipNumberMail");
