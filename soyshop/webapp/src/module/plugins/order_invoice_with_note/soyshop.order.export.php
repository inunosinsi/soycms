<?php


class OrderInvoiceWithNoteExport extends SOYShopOrderExportBase{

	/**
	 * 検索結果一覧に表示するメニューの表示文言
	 */
	function getMenuTitle(){
		return "納品書一括作成(控え有り)";
	}

	/**
	 * 検索結果一覧に表示するメニューの説明
	 */
	function getMenuDescription(){
		return "注文結果に表示されている注文の印刷用納品書(控え有り)を一括で作成します。";
	}

	/**
	 * export エクスポート実行
	 */
	function export(array $orders){
		if(!defined("ORDER_DOCUMENT_MODE")) define("ORDER_DOCUMENT_MODE", "delivery");
		if(!defined("ORDER_DOCUMENT_LABEL")) define("ORDER_DOCUMENT_LABEL", "納品書");
		if(!defined("ORDER_TEMPLATE")) define("ORDER_TEMPLATE", "default");
		$html = file_get_contents(dirname(__FILE__) . "/template/" . ORDER_TEMPLATE . ".html");

		SOY2DAOFactory::create("order.SOYShop_ItemModule");
		SOY2DAOFactory::create("config.SOYShop_ShopConfig");

		SOY2::import("module.plugins.order_invoice_with_note.page.ContinuousPage");
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
SOYShopPlugin::extension("soyshop.order.export", "order_invoice_with_note", "OrderInvoiceWithNoteExport");
