<?php
/*
 */
class PrintShippingLabelExport extends SOYShopOrderExportBase{

	/**
	 * 検索結果一覧に表示するメニューの表示文言
	 */
	function getMenuTitle(){
		return "配送伝票一括生成";
	}

	/**
	 * 検索結果一覧に表示するメニューの説明
	 */
	function getMenuDescription(){
		return "注文結果に表示されている注文の印刷用配送伝票を一括で作成します。";
	}

	/**
	 * export エクスポート実行
	 */
	function export($orders){
		SOY2::import("module.plugins.order_invoice.common.OrderInvoiceCommon");
//		$template = OrderInvoiceCommon::getTemplateName();
		$tmp = "kuroneko";
		$html = file_get_contents(dirname(__FILE__) . "/template/" . $tmp . ".html");
		
//		SOY2DAOFactory::create("order.SOYShop_ItemModule");
//		SOY2DAOFactory::create("config.SOYShop_ShopConfig");
		
		SOY2::import("module.plugins.print_shipping_label.page.ContinuousPage");
		$page = SOY2HTMLFactory::createInstance("ContinuousPage", array(
			"arguments" => array("main_print", $html),
			"orders" => $orders
		));		
		
		$page->setTitle("配送伝票");
		$page->build_print();
		
		ob_start();
		$page->display();
		$html = ob_get_contents();
		ob_end_clean();
		
		echo  $html;
	}
}
SOYShopPlugin::extension("soyshop.order.export", "print_shipping_label", "PrintShippingLabelExport");
?>