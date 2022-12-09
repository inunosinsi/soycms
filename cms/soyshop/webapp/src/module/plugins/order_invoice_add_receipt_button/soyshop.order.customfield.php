<?php

class OrderInvoiceAddReceiptCustomfieldModule extends SOYShopOrderCustomfield{

	function display(int $orderId){
		$outputDate = soyshop_get_order_date_attribute_value($orderId, "order_invoice_mode_receipt", "int");
		if(is_numeric($outputDate) && $outputDate > 0){
			return array(array("name" => "領収書の最終出力日", "value" => date("Y-m-d H:i:s", $outputDate)));
		}
		return array();
	}
}
SOYShopPlugin::extension("soyshop.order.customfield", "order_invoice_add_receipt_button", "OrderInvoiceAddReceiptCustomfieldModule");
