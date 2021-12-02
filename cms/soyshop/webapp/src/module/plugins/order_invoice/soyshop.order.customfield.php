<?php

class OrderInvoiceCustomfieldModule extends SOYShopOrderCustomfield{

	function display(int $orderId){
		$outputDate = soyshop_get_order_date_attribute_value($orderId, "order_invoice_mode_delivery", "int");
		if(is_numeric($outputDate)){
			return array(array("name" => "納品書の最終出力日", "value" => date("Y-m-d H:i:s", $outputDate)));
		}
		return array();
	}
}
SOYShopPlugin::extension("soyshop.order.customfield", "order_invoice", "OrderInvoiceCustomfieldModule");
