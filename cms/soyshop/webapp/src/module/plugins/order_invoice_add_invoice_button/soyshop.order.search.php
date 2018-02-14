<?php

class OrderInvoiceAddInvoiceSearch extends SOYShopOrderSearch{

	function setParameter($params){
		$where = array();
		$binds = array();
		$queries = array();

		if(isset($params["orderInvoiceStart"]) && strlen($params["orderInvoiceStart"])){
			$where[] = "order_value_1 > :order_invoice_start";
			$binds[":order_invoice_start"] = soyshop_convert_timestamp($params["orderInvoiceStart"]);
		}

		if(isset($params["orderInvoiceEnd"]) && strlen($params["orderInvoiceEnd"])){
			$where[] = "order_value_1 < :order_invoice_end";
			$binds[":order_invoice_end"] = soyshop_convert_timestamp($params["orderInvoiceEnd"], "end");
		}

		if(count($where)){
			$queries[] = "id IN (SELECT order_id FROM soyshop_order_date_attribute WHERE order_field_id = 'order_invoice_mode_invoice' AND " . implode(" AND ", $where) . ")";
		}

		//未出力
		if(isset($params["noOrderInvoice"]) && $params["noOrderInvoice"] == 1){
			$queries[] = "id NOT IN (SELECT order_id FROM soyshop_order_date_attribute WHERE order_field_id = 'order_invoice_mode_invoice')";
		}

		if(count($queries)) return array("queries" => $queries, "binds" => $binds);
	}

	function searchItems($params){
		$start = (isset($params["orderInvoiceStart"])) ? $params["orderInvoiceStart"] : "";
		$end = (isset($params["orderInvoiceEnd"])) ? $params["orderInvoiceEnd"] : "";

		$html = array();
		$html[] = "最終出力日：";
		$html[] = "<input name=\"search[customs][order_invoice_add_invoice_button][orderInvoiceStart]\" type=\"text\" class=\"date_picker_start\" value=\"" . $start . "\">";
		$html[] = "～";
		$html[] = "<input name=\"search[customs][order_invoice_add_invoice_button][orderInvoiceEnd]\" type=\"text\" class=\"date_picker_end\" value=\"" . $end . "\">";
		if(isset($params["noOrderInvoice"]) && $params["noOrderInvoice"] == 1){
			$html[] = "<label><input type=\"checkbox\" name=\"search[customs][order_invoice_add_invoice_button][noOrderInvoice]\" value=\"1\" checked=\"checked\">未出力</label>";
		}else{
			$html[] = "<label><input type=\"checkbox\" name=\"search[customs][order_invoice_add_invoice_button][noOrderInvoice]\" value=\"1\">未出力</label>";
		}


		return array("label" => "請求書", "form" => implode("\n", $html));
	}
}
SOYShopPlugin::extension("soyshop.order.search", "order_invoice_add_invoice_button", "OrderInvoiceAddInvoiceSearch");
