<?php

class OrderInvoiceAddInvoiceButtonExport extends SOYShopOrderExportBase{

	/**
	 * 検索結果一覧に表示するメニューの表示文言
	 */
	function getMenuTitle(){
		return "請求書一括作成";
	}

	/**
	 * 検索結果一覧に表示するメニューの説明
	 */
	function getMenuDescription(){
		return "注文結果に表示されている注文の印刷用請求書を一括で作成します。";
	}

	/**
	 * export エクスポート実行
	 */
	function export(array $orders){
		if(!defined("OUTPUT_INVOICE_MODE")) define("OUTPUT_INVOICE_MODE", "ex");	// fn(soyshop.order.function) or ex(soyshop.order.ex)
		if(!defined("ORDER_DOCUMENT_MODE")) define("ORDER_DOCUMENT_MODE", "invoice");
		if(!defined("ORDER_DOCUMENT_LABEL")) define("ORDER_DOCUMENT_LABEL", "請求書");

		SOY2::import("module.plugins.order_invoice.common.OrderInvoiceCommon");

		if(!defined("ORDER_TEMPLATE")) define("ORDER_TEMPLATE", OrderInvoiceCommon::getTemplateName());
		$html = file_get_contents(dirname(dirname(__FILE__)) . "/order_invoice/template/" . ORDER_TEMPLATE . ".html");

		SOY2DAOFactory::create("order.SOYShop_ItemModule");
		SOY2DAOFactory::create("config.SOYShop_ShopConfig");

		SOY2::import("module.plugins.order_invoice.page.ContinuousPage");
		$page = SOY2HTMLFactory::createInstance("ContinuousPage", array(
			"arguments" => array("main_print", $html),
			"orders" => $orders
		));

		$page->setTitle(ORDER_DOCUMENT_LABEL);
		$page->build_print();

		ob_start();
		$page->display();
		$html = ob_get_contents();
		ob_end_clean();

		echo  $html;
	}
}
SOYShopPlugin::extension("soyshop.order.export", "order_invoice_add_invoice_button", "OrderInvoiceAddInvoiceButtonExport");
?>
