<?php
class AffiliateA8flyCart extends SOYShopCartBase{

	function displayCompletePage(CartLogic $cart){
		$items = $cart->getItems();
		if(!count($items)) return "";

		SOY2::import("module.plugins.affiliate_a8fly.util.AffiliateA8flyUtil");

		$config = AffiliateA8flyUtil::getConfig();
		if(!isset($config["id"]) || !strlen($config["id"])) return "";

		$trackingNumber = SOY2Logic::createInstance("logic.order.OrderLogic")->getById($items[0]->getOrderId())->getTrackingNumber();

		$total = 0;

		$html = array();

		$html[] = "<span id=\"a8sales\"></span>";
		//</head>直前に移動
		//$html[] = "<script src=\"//statics.a8.net/a8sales/a8sales.js\"></script>";
		$html[] = "<script>";
		$html[] = "a8sales({";
  		$html[] = "	\"pid\": \"" . trim($config["id"]) . "\",";
		$html[] = "	\"order_number\": \"" . $trackingNumber . "\",";
		$html[] = "	\"currency\": \"JPY\",";
		$html[] = "	\"items\": [";
		foreach($items as $item){
			$html[] = "		{";
			$html[] = "			\"code\": \"" . soyshop_get_item_object($item->getItemId())->getCode() . "\",";
			$html[] = "			\"price\": " . $item->getItemPrice() . ",";
			$html[] = "			\"quantity\": " . $item->getItemCount() . "";
			$html[] = "		},";
			$total += (int)$item->getItemPrice() * (int)$item->getItemCount();
		}

		$html[] = "	],";
		$html[] = "	\"total_price\": " . $total;
		$html[] = "});";
		$html[] = "</script>";

		return implode("\n", $html);
	}
}
SOYShopPlugin::extension("soyshop.cart","affiliate_a8fly","AffiliateA8flyCart");
