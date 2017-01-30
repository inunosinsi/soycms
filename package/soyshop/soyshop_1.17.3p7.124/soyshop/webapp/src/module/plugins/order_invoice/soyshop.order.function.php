<?php

class SOYShopMainInvoiceFunction extends SOYShopOrderFunction{
	
	/**
	 * title text
	 */
	function getTitle(){
		return "納品書";
	}
	
	/**
	 * @return html
	 */
	function getPage(){
		if(!defined("ORDER_DOCUMENT_LABEL")) define("ORDER_DOCUMENT_LABEL", "納品書");
		
		SOY2::import("module.plugins.order_invoice.common.OrderInvoiceCommon");
		
		if(!defined("ORDER_TEMPLATE")) define("ORDER_TEMPLATE", OrderInvoiceCommon::getTemplateName());
		$html = file_get_contents(dirname(__FILE__) . "/template/" . ORDER_TEMPLATE . ".html");
		
		SOY2DAOFactory::create("order.SOYShop_ItemModule");
		SOY2DAOFactory::create("config.SOYShop_ShopConfig");
		
		SOY2::import("module.plugins.order_invoice.page.InvoicePage");
		$page = SOY2HTMLFactory::createInstance("InvoicePage", array(
			"arguments" => array("main_invoice", $html),
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
SOYShopPlugin::extension("soyshop.order.function", "order_invoice", "SOYShopMainInvoiceFunction");