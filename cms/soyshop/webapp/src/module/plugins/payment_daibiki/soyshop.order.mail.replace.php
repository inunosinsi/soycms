<?php

class PaymentDaibikiMailReplace extends SOYShopOrderMailReplace{

	function strings(){
		return array(
			"DAIBIKI_FEE" => "代引き手数料"
		);
	}

	function replace(SOYShop_Order $order, string $content){
		//代引き手数料
		$list = $order->getModuleList();
		$fee = (isset($list["payment_daibiki"])) ? soy2_number_format($list["payment_daibiki"]->getPrice()) : "";
		return str_replace("#DAIBIKI_FEE#", $fee, $content);
	}
}

SOYShopPlugin::extension("soyshop.order.mail.replace", "payment_daibiki", "PaymentDaibikiMailReplace");
