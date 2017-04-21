<?php

class SlipNumberMailReplace extends SOYShopOrderMailReplace{

	function strings(){
		return array(
			"SLIP_NUMBER" => "伝票番号"
		);
	}

	function replace(SOYShop_Order $order, $content){
		$slipNumber = "";
		
		$attr = SOY2Logic::createInstance("module.plugins.slip_number.logic.SlipNumberLogic")->getAttribute($order->getId());
		if(strlen($attr->getValue1())){
			SOY2::import("module.plugins.slip_number.util.SlipNumberUtil");
			
			$slipNumber = $attr->getValue1();
		}
		
		return str_replace("#SLIP_NUMBER#", $slipNumber, $content);
	}
}

SOYShopPlugin::extension("soyshop.order.mail.replace", "slip_number", "SlipNumberMailReplace");
