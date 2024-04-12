<?php
class OrderInvoiceAddReceiptButtonFunction extends SOYShopOrderFunction{

	/**
	 * title text
	 */
	function getTitle(){
		return "領収書";
	}

	/**
	 * @return html
	 */
	function getPage(){
		if(!defined("OUTPUT_INVOICE_MODE")) define("OUTPUT_INVOICE_MODE", "fn");	// fn(soyshop.order.function) or ex(soyshop.order.ex)
		if(!defined("ORDER_DOCUMENT_MODE")) define("ORDER_DOCUMENT_MODE", "receipt");
		if(!defined("ORDER_DOCUMENT_LABEL")) define("ORDER_DOCUMENT_LABEL", "領収書");

		//インボイス 適格請求書発行事業者登録モード
		if(!defined("INVOICE_NUMBER_MODE")) define("INVOICE_NUMBER_MODE", (strlen((string)(string)SOYShop_ShopConfig::load()->getInvoiceNumber()) === 13));
		
		SOY2::import("module.plugins.order_invoice.common.OrderInvoiceCommon");

		if(!defined("ORDER_TEMPLATE")) define("ORDER_TEMPLATE", OrderInvoiceCommon::getTemplateName());
		$html = file_get_contents(dirname(dirname(__FILE__)) . "/order_invoice/template/" . ORDER_TEMPLATE . ".html");

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
SOYShopPlugin::extension("soyshop.order.function", "order_invoice_add_receipt_button", "OrderInvoiceAddReceiptButtonFunction");
