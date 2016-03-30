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
		SOY2::import("module.plugins.order_invoice.common.OrderInvoiceCommon");
		$template = OrderInvoiceCommon::getTemplateName();
		$html = file_get_contents(dirname(__FILE__) . "/template/" . $template . ".html");
		
		SOY2DAOFactory::create("order.SOYShop_ItemModule");
		SOY2DAOFactory::create("config.SOYShop_ShopConfig");
		
		include_once(dirname(__FILE__) . "/page/InvoicePage.class.php");	
		$page = SOY2HTMLFactory::createInstance("InvoicePage", array(
			"arguments" => array("main_invoice", $html),
			"orderId" => $this->getOrderId()
		));
		
		$page->setTitle("納品書");
		$page->build_invoice();
		
		ob_start();
		$page->display();
		$html = ob_get_contents();
		ob_end_clean();
		
		return $html;
	}
}
SOYShopPlugin::extension("soyshop.order.function", "order_invoice", "SOYShopMainInvoiceFunction");