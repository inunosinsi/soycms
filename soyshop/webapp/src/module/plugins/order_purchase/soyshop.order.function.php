<?php

class OrderPurchaseFunction extends SOYShopOrderFunction{
	
	/**
	 * title text
	 */
	function getTitle(){
		return "発注書";
	}
	
	/**
	 * @return html
	 */
	function getPage(){
		if(!defined("ORDER_DOCUMENT_LABEL")) define("ORDER_DOCUMENT_LABEL", "発注書");
		
//		SOY2::import("module.plugins.order_purchase.util.OrderPurchaseUtil");
		
		if(!defined("ORDER_TEMPLATE")) define("ORDER_TEMPLATE", "simple");
		$html = file_get_contents(dirname(__FILE__) . "/template/" . ORDER_TEMPLATE . ".html");
		
		SOY2DAOFactory::create("order.SOYShop_ItemModule");
		SOY2DAOFactory::create("config.SOYShop_ShopConfig");
		
		SOY2::import("module.plugins.order_purchase.page.PurchasePage");
		$page = SOY2HTMLFactory::createInstance("PurchasePage", array(
			"arguments" => array("main_purchase", $html),
			"orderId" => $this->getOrderId()
		));
		
		$page->setTitle(ORDER_DOCUMENT_LABEL);
		$page->build_invoice();
		
		ob_start();
		$page->display();
		$html = ob_get_contents();
		ob_end_clean();
		
		return $html;
	}
}
SOYShopPlugin::extension("soyshop.order.function", "order_purchase", "OrderPurchaseFunction");