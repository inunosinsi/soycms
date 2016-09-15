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
		SOY2::import("module.plugins.print_shipping_label.form.ShippingLabelFormPage");
		$form = SOY2HTMLFactory::createInstance("ShippingLabelFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * export エクスポート実行
	 */
	function export($orders){
		SOY2::import("module.plugins.print_shipping_label.util.PrintShippingLabelUtil");
		
		$tmp = key($_POST["ShippingLabel"]);
		$html = file_get_contents(dirname(__FILE__) . "/template/" . $tmp . ".html");
		
		//何の伝票を印刷するか？
		define("SHIPPING_LABEL_COMPANY", $tmp);
		define("SHIPPING_LABEL_TYPE", $_POST["ShippingLabel"][$tmp]);
		
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