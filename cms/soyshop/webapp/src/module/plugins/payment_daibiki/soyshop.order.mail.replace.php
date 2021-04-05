<?php

class PaymentDaibikiMailReplace extends SOYShopOrderMailReplace{

	function strings(){
		return array(
			"DAIBIKI_FEE" => "代引き手数料"
		);
	}

	function replace(SOYShop_Order $order, $content){

		//代引き手数料
		$list = $order->getModuleList();
		$fee = (isset($list["payment_daibiki"])) ? soy2_number_format($list["payment_daibiki"]->getPrice()) : "";
		$content = str_replace("#DAIBIKI_FEE#", $fee, $content);
		
		return $content;
	}
}

SOYShopPlugin::extension("soyshop.order.mail.replace", "payment_daibiki", "PaymentDaibikiMailReplace");
