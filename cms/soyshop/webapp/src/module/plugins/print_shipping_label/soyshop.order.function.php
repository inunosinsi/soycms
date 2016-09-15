<?php

class PrintShippingLabelFunction extends SOYShopOrderFunction{
	
	/**
	 * title text
	 */
	function getTitle(){
		return "配送伝票";
	}
	
	/**
	 * @return html
	 */
	function getPage(){
		SOY2::import("module.plugins.order_invoice.common.OrderInvoiceCommon");
//		$template = OrderInvoiceCommon::getTemplateName();
		$tmp = "kuroneko";
		$html = file_get_contents(dirname(__FILE__) . "/template/" . $tmp . ".html");
		
//		SOY2DAOFactory::create("order.SOYShop_ItemModule");
//		SOY2DAOFactory::create("config.SOYShop_ShopConfig");
		
		SOY2::import("module.plugins.print_shipping_label.page.ShippingLabelPage");
		$page = SOY2HTMLFactory::createInstance("ShippingLabelPage", array(
			"arguments" => array("main_label", $html),
			"orderId" => $this->getOrderId()
		));
		
		$page->setTitle("配送伝票");
		$page->build_label();
		
		ob_start();
		$page->display();
		$html = ob_get_contents();
		ob_end_clean();
		
		return $html;
	}
}
SOYShopPlugin::extension("soyshop.order.function", "print_shipping_label", "PrintShippingLabelFunction");