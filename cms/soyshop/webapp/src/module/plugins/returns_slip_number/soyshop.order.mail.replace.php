<?php

class ReturnsSlipNumberMailReplace extends SOYShopOrderMailReplace{

	function strings(){
		return array(
			"RETURNS_SLIP_NUMBER" => "返送伝票番号"
		);
	}

	function replace(SOYShop_Order $order, $content){
		SOY2::import("module.plugins.returns_slip_number.util.ReturnsSlipNumberUtil");
		$slipNumber = soyshop_get_order_attribute_value($order->getId(), ReturnsSlipNumberUtil::PLUGIN_ID, "string");
		return str_replace("#RETURNS_SLIP_NUMBER#", $slipNumber, $content);
	}
}

SOYShopPlugin::extension("soyshop.order.mail.replace", "returns_slip_number", "ReturnsSlipNumberMailReplace");
