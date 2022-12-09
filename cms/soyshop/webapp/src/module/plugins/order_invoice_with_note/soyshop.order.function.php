<?php

class OrderInvoiceWithNoteFunction extends SOYShopOrderFunction{

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
		if(!defined("ORDER_DOCUMENT_MODE")) define("ORDER_DOCUMENT_MODE", "delivery");
		if(!defined("ORDER_DOCUMENT_LABEL")) define("ORDER_DOCUMENT_LABEL", "納品書");
		if(!defined("ORDER_TEMPLATE")) define("ORDER_TEMPLATE", "default");
		$html = file_get_contents(dirname(__FILE__) . "/template/" . ORDER_TEMPLATE . ".html");

		SOY2DAOFactory::create("order.SOYShop_ItemModule");
		SOY2DAOFactory::create("config.SOYShop_ShopConfig");

		SOY2::import("module.plugins.order_invoice_with_note.page.InvoicePage");
		$page = SOY2HTMLFactory::createInstance("InvoicePage", array(
			"arguments" => array("main_note", $html),
			"orderId" => $this->getOrderId()
		));

		$page->setTitle(ORDER_DOCUMENT_LABEL);
		$page->build_note();

		ob_start();
		$page->display();
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}
}
SOYShopPlugin::extension("soyshop.order.function", "order_invoice_with_note", "OrderInvoiceWithNoteFunction");
