<?php

class TakeOverSetLinkCart extends SOYShopCartBase{

	function displayCompletePage(CartLogic $cart){
		$order = soyshop_get_order_object($cart->getAttribute("order_id"));
		if(is_null($order->getId())) return "";

		SOY2::import("module.plugins.take_over_set_link.util.TakeOverLinkUtil");
		$cnf = TakeOverLinkUtil::getConfig();
		if(!strlen($cnf["url"]) || !is_numeric($cnf["timeout"])) return "";

		$url = $cnf["url"];
		$url .= "?ctk=" . $order->getTrackingNumber() . "-" . md5($order->getId() + $order->getUserId() + $order->getOrderDate());

		$desp = str_replace("##TAKE_OVER_URL##", $url, $cnf["description"]);

		$html = array();
		$html[] = $desp;
		$html[] = "<script>";
		$html[] = "var takeover_destination_timeout = " . (int)$cnf["timeout"] . ";";
		$html[] = "var takeover_destination_url = \"" . $url . "\";";
		$html[] = file_get_contents(dirname(__FILE__) . "/js/timeout.js");
		$html[] = "</script>";

		return implode("\n", $html);
	}
}
SOYShopPlugin::extension("soyshop.cart", "take_over_set_link", "TakeOverSetLinkCart");
