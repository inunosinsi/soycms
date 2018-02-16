<?php

class CustomSearchFormComponent {

	public static function parameter($params, $moduleId, $propName, $mode){
		$where = array();
		$binds = array();
		$queries = array();

		if(isset($params[$propName . "Start"]) && strlen($params[$propName . "Start"])){
			$where[] = "order_value_1 > :" . $moduleId . "_start";
			$binds[":" . $moduleId . "_start"] = soyshop_convert_timestamp($params[$propName . "Start"]);
		}

		if(isset($params[$propName . "End"]) && strlen($params[$propName . "End"])){
			$where[] = "order_value_1 < :" . $moduleId . "_end";
			$binds[":" . $moduleId . "_end"] = soyshop_convert_timestamp($params[$propName . "End"], "end");
		}

		if(count($where)){
			$queries[] = "id IN (SELECT order_id FROM soyshop_order_date_attribute WHERE order_field_id = 'order_invoice_mode_" . $mode . "' AND " . implode(" AND ", $where) . ")";
		}

		//未出力
		$propName = ucfirst($propName);
		if(isset($params["no" . $propName]) && $params["no" . $propName] == 1){
			$queries[] = "id NOT IN (SELECT order_id FROM soyshop_order_date_attribute WHERE order_field_id = 'order_invoice_mode_" . $mode . "')";
		}

		return array($queries, $binds);
	}

	public static function buildSearchForm($params, $moduleId, $propName){
		$start = (isset($params[$propName . "Start"])) ? $params[$propName . "Start"] : "";
		$end = (isset($params[$propName . "End"])) ? $params[$propName . "End"] : "";

		$html = array();
		$html[] = "最終出力日：";
		$html[] = "<input name=\"search[customs][" . $moduleId . "][" . $propName . "Start]\" type=\"text\" class=\"date_picker_start\" value=\"" . $start . "\">";
		$html[] = "～";
		$html[] = "<input name=\"search[customs][" . $moduleId . "][" . $propName . "End]\" type=\"text\" class=\"date_picker_end\" value=\"" . $end . "\">";

		//未出力
		$propName = ucfirst($propName);
		if(isset($params["no" . $propName]) && $params["no" . $propName] == 1){
			$html[] = "<label><input type=\"checkbox\" name=\"search[customs][" . $moduleId . "][no" . $propName . "]\" value=\"1\" checked=\"checked\">未出力</label>";
		}else{
			$html[] = "<label><input type=\"checkbox\" name=\"search[customs][" . $moduleId . "][no" . $propName . "]\" value=\"1\">未出力</label>";
		}

		return implode("\n", $html);
	}
}
