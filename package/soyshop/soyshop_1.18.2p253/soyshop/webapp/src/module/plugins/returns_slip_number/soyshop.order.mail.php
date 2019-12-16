<?php

class ReturnsSlipNumberMail extends SOYShopOrderMail{

	/**
	 * メール本文を取得
	 * @return string
	 */
	function getMailBody(SOYShop_Order $order){

		$attr = SOY2Logic::createInstance("module.plugins.returns_slip_number.logic.ReturnsSlipNumberLogic")->getAttribute($order->getId());
		if(strlen($attr->getValue1())){
			SOY2::import("module.plugins.returns_slip_number.util.ReturnsSlipNumberUtil");
			$config = ReturnsSlipNumberUtil::getConfig();

			$content = (isset($config["content"])) ? $config["content"] : "";
			return str_replace("#RETURNS_SLIP_NUMBER#", $attr->getValue1(), $content);
		}
	}


	function getDisplayOrder(){
		return 100;//delivery系は200番台
	}
}

SOYShopPlugin::extension("soyshop.order.mail.user", "returns_slip_number", "ReturnsSlipNumberMail");
SOYShopPlugin::extension("soyshop.order.mail.confirm", "returns_slip_number", "ReturnsSlipNumberMail");
SOYShopPlugin::extension("soyshop.order.mail.payment", "returns_slip_number", "ReturnsSlipNumberMail");
SOYShopPlugin::extension("soyshop.order.mail.delivery", "returns_slip_number", "ReturnsSlipNumberMail");
SOYShopPlugin::extension("soyshop.order.mail.admin", "returns_slip_number", "ReturnsSlipNumberMail");
