<?php

class ReturnsSlipNumberMail extends SOYShopOrderMail{

	/**
	 * メール本文を取得
	 * @return string
	 */
	function getMailBody(SOYShop_Order $order){
		SOY2::import("module.plugins.returns_slip_number.util.ReturnsSlipNumberUtil");
		$slipNumber = soyshop_get_order_attribute_value($order->getId(), ReturnsSlipNumberUtil::PLUGIN_ID, "string");
		if(strlen($slipNumber)){
			$cnf = ReturnsSlipNumberUtil::getConfig();

			$content = (isset($cnf)) ? $cnf : "";
			return str_replace("#RETURNS_SLIP_NUMBER#", $slipNumber, $content);
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
