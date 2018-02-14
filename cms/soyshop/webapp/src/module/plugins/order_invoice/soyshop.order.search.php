<?php

class OrderInvoiceSearch extends SOYShopOrderSearch{

	function setParameter($params){
		$where = array();
		$binds = array();
		$queries = array();

		if(isset($params["deliveryNoteStart"]) && strlen($params["deliveryNoteStart"])){
			$where[] = "order_value_1 > :delivery_note_start";
			$binds[":delivery_note_start"] = soyshop_convert_timestamp($params["deliveryNoteStart"]);
		}

		if(isset($params["deliveryNoteEnd"]) && strlen($params["deliveryNoteEnd"])){
			$where[] = "order_value_1 < :delivery_note_end";
			$binds[":delivery_note_end"] = soyshop_convert_timestamp($params["deliveryNoteEnd"], "end");
		}

		if(count($where)){
			$queries[] = "id IN (SELECT order_id FROM soyshop_order_date_attribute WHERE order_field_id = 'order_invoice_mode_delivery' AND " . implode(" AND ", $where) . ")";
		}

		//未出力
		if(isset($params["noDeliveryNote"]) && $params["noDeliveryNote"] == 1){
			$queries[] = "id NOT IN (SELECT order_id FROM soyshop_order_date_attribute WHERE order_field_id = 'order_invoice_mode_delivery')";
		}

		if(count($queries)) return array("queries" => $queries, "binds" => $binds);
	}

	function searchItems($params){
		$start = (isset($params["deliveryNoteStart"])) ? $params["deliveryNoteStart"] : "";
		$end = (isset($params["deliveryNoteEnd"])) ? $params["deliveryNoteEnd"] : "";

		$html = array();
		$html[] = "最終出力日：";
		$html[] = "<input name=\"search[customs][order_invoice][deliveryNoteStart]\" type=\"text\" class=\"date_picker_start\" value=\"" . $start . "\">";
		$html[] = "～";
		$html[] = "<input name=\"search[customs][order_invoice][deliveryNoteEnd]\" type=\"text\" class=\"date_picker_end\" value=\"" . $end . "\">";
		if(isset($params["noDeliveryNote"]) && $params["noDeliveryNote"] == 1){
			$html[] = "<label><input type=\"checkbox\" name=\"search[customs][order_invoice][noDeliveryNote]\" value=\"1\" checked=\"checked\">未出力</label>";
		}else{
			$html[] = "<label><input type=\"checkbox\" name=\"search[customs][order_invoice][noDeliveryNote]\" value=\"1\">未出力</label>";
		}


		return array("label" => "納品書", "form" => implode("\n", $html));
	}
}
SOYShopPlugin::extension("soyshop.order.search", "order_invoice", "OrderInvoiceSearch");
