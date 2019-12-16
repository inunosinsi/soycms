<?php
/*
 */
class SOYShopATypeInvoiceExport extends SOYShopOrderExportBase{

	/**
	 * 検索結果一覧に表示するメニューの表示文言
	 */
	function getMenuTitle(){
		return "納品書A一括作成";
	}

	/**
	 * 検索結果一覧に表示するメニューの説明
	 */
	function getMenuDescription(){
		return "注文結果に表示されている注文の印刷用納品書を一括で作成します。";
	}

	/**
	 * export エクスポート実行
	 */
	function export($orders){
		$html = file_get_contents(dirname(__FILE__) . "/template.html");
		
		SOY2DAOFactory::create("order.SOYShop_ItemModule");
		SOY2DAOFactory::create("config.SOYShop_ShopConfig");
		
		include_once(dirname(__FILE__) . "/ContinuousPrint.class.php");
		
		$page = SOY2HTMLFactory::createInstance("Print_IndexPage", array(
			"arguments" => array("main_print",$html),
			"orders" => $orders
		));		
		
		$page->setTitle("納品書A");
		$page->build_print();
		
//		ob_start();
		return $page->display();
//		$html = ob_get_contents();
//		ob_end_clean();
		
//		return $html;
	}

}

SOYShopPlugin::extension("soyshop.order.export","order_invoice_a","SOYShopATypeInvoiceExport");
?>
