<?php

class OrderInvoiceAddReceiptSearch extends SOYShopOrderSearch{

	function setParameter($params){
		$where = array();
		$binds = array();
		$queries = array();

		if(isset($params["orderReceiptStart"]) && strlen($params["orderReceiptStart"])){
			$where[] = "order_value_1 > :order_receipt_start";
			$binds[":order_receipt_start"] = soyshop_convert_timestamp($params["orderReceiptStart"]);
		}

		if(isset($params["orderReceiptEnd"]) && strlen($params["orderReceiptEnd"])){
			$where[] = "order_value_1 < :order_receipt_end";
			$binds[":order_receipt_end"] = soyshop_convert_timestamp($params["orderReceiptEnd"], "end");
		}

		if(count($where)){
			$queries[] = "id IN (SELECT order_id FROM soyshop_order_date_attribute WHERE order_field_id = 'order_invoice_mode_receipt' AND " . implode(" AND ", $where) . ")";
		}

		//未出力
		if(isset($params["noOrderReceipt"]) && $params["noOrderReceipt"] == 1){
			$queries[] = "id NOT IN (SELECT order_id FROM soyshop_order_date_attribute WHERE order_field_id = 'order_invoice_mode_receipt')";
		}

		if(count($queries)) return array("queries" => $queries, "binds" => $binds);
	}

	function searchItems($params){
		$start = (isset($params["orderReceiptStart"])) ? $params["orderReceiptStart"] : "";
		$end = (isset($params["orderReceiptEnd"])) ? $params["orderReceiptEnd"] : "";

		$html = array();
		$html[] = "最終出力日：";
		$html[] = "<input name=\"search[customs][order_invoice_add_receipt_button][orderReceiptStart]\" type=\"text\" class=\"date_picker_start\" value=\"" . $start . "\">";
		$html[] = "～";
		$html[] = "<input name=\"search[customs][order_invoice_add_receipt_button][orderReceiptEnd]\" type=\"text\" class=\"date_picker_end\" value=\"" . $end . "\">";
		if(isset($params["noOrderReceipt"]) && $params["noOrderReceipt"] == 1){
			$html[] = "<label><input type=\"checkbox\" name=\"search[customs][order_invoice_add_receipt_button][noOrderReceipt]\" value=\"1\" checked=\"checked\">未出力</label>";
		}else{
			$html[] = "<label><input type=\"checkbox\" name=\"search[customs][order_invoice_add_receipt_button][noOrderReceipt]\" value=\"1\">未出力</label>";
		}


		return array("label" => "領収書", "form" => implode("\n", $html));
	}
}
SOYShopPlugin::extension("soyshop.order.search", "order_invoice_add_receipt_button", "OrderInvoiceAddReceiptSearch");
