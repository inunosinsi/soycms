<?php

class SlipNumberMail extends SOYShopOrderMail{

	/**
	 * メール本文を取得
	 * @return string
	 */
	function getMailBody(SOYShop_Order $order){
		
		$attr = SOY2Logic::createInstance("module.plugins.slip_number.logic.SlipNumberLogic")->getAttribute($order->getId());
		if(strlen($attr->getValue1())){
			SOY2::import("module.plugins.slip_number.util.SlipNumberUtil");
			$config = SlipNumberUtil::getConfig();
			
			$content = (isset($config["content"])) ? $config["content"] : "";
			return str_replace("#SLIP_NUMBER#", $attr->getValue1(), $content);
		}
	}
	

	function getDisplayOrder(){
		return 100;//delivery系は200番台
	}
}

SOYShopPlugin::extension("soyshop.order.mail.user", "slip_number", "SlipNumberMail");
SOYShopPlugin::extension("soyshop.order.mail.confirm", "slip_number", "SlipNumberMail");
SOYShopPlugin::extension("soyshop.order.mail.admin", "slip_number", "SlipNumberMail");