<?php

class LoggingSlipNumberMail extends SOYShopOrderMail{

	/**
	 * メール本文を取得
	 * @return string
	 */
	function getMailBody(SOYShop_Order $order){
		
		$attr = SOY2Logic::createInstance("module.plugins.logging_slip_number.logic.SlipNumberLogic")->getAttribute($order->getId());
		if(strlen($attr->getValue1())){
			SOY2::import("module.plugins.logging_slip_number.util.LoggingSlipNumberUtil");
			$config = LoggingSlipNumberUtil::getConfig();
			
			$content = (isset($config["content"])) ? $config["content"] : "";
			return str_replace("#SLIP_NUMBER#", $attr->getValue1(), $content);
		}
	}
	

	function getDisplayOrder(){
		return 100;//delivery系は200番台
	}
}

SOYShopPlugin::extension("soyshop.order.mail.user", "logging_slip_number", "LoggingSlipNumberMail");
SOYShopPlugin::extension("soyshop.order.mail.confirm", "logging_slip_number", "LoggingSlipNumberMail");
SOYShopPlugin::extension("soyshop.order.mail.admin", "logging_slip_number", "LoggingSlipNumberMail");