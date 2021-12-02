<?php

class SlipNumberMailReplace extends SOYShopOrderMailReplace{

	function strings(){
		return array(
			"SLIP_NUMBER" => "伝票番号"
		);
	}

	function replace(SOYShop_Order $order, $content){
		$slipNumberChain = SOY2Logic::createInstance("module.plugins.slip_number.logic.SlipNumberLogic")->getSlipNumberByOrderId($order->getId());
		return str_replace("#SLIP_NUMBER#", $slipNumberChain, $content);
	}
}

SOYShopPlugin::extension("soyshop.order.mail.replace", "slip_number", "SlipNumberMailReplace");
