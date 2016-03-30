<?php

class UtilMultiLanguageOrderComplete extends SOYShopOrderComplete{

	function execute(SOYShop_Order $order){
		$orderDao = SOY2DAOFactory::create("order.SOYShop_OrderDAO");
		
		$order->setAttribute("util_multi_language", array(
			"name" => "注文時の言語設定",
			"value" => SOYSHOP_PUBLISH_LANGUAGE,
			"readonly" => true,
	    	"hidden" => true
		));
		
		try{
			$orderDao->updateStatus($order);
		}catch(Exception $e){
			//		
		}
	}
}

SOYShopPlugin::extension("soyshop.order.complete", "util_multi_language", "UtilMultiLanguageOrderComplete");
?>