<?php

class ReturnsSlipNumberMailReplace extends SOYShopOrderMailReplace{

	function strings(){
		return array(
			"SLIP_NUMBER" => "返送伝票番号"
		);
	}

	function replace(SOYShop_Order $order, $content){
		$slipNumber = "";

		$attr = SOY2Logic::createInstance("module.plugins.returns_slip_number.logic.ReturnsSlipNumberLogic")->getAttribute($order->getId());
		if(strlen($attr->getValue1())){
			SOY2::import("module.plugins.returns_slip_number.util.ReturnsSlipNumberUtil");

			$slipNumber = $attr->getValue1();
		}

		return str_replace("#_RETURNS_SLIP_NUMBER#", $slipNumber, $content);
	}
}

SOYShopPlugin::extension("soyshop.order.mail.replace", "returns_slip_number", "ReturnsSlipNumberMailReplace");
