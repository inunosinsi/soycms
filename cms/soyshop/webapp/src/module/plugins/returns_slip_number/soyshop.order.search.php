<?php

class ReturnsSlipNumberSearch extends SOYShopOrderSearch{

	function setParameter($params){
		$slipNumber = self::getSlipNumber($params);
		if(strlen($slipNumber)){
			$queries[] = "id IN (SELECT order_id FROM soyshop_order_attribute WHERE order_field_id = 'returns_slip_number_plugin' AND order_value1 LIKE :ReturnsSlipNumber)";
			$binds[":ReturnsSlipNumber"] = "%" . $slipNumber . "%";
			return array("queries" => $queries, "binds" => $binds);
		}
	}

	function searchItems($params){
		$html = "<input type=\"text\" name=\"search[customs][returns_slip_number]\" value=\"" . self::getSlipNumber($params) . "\">";
		return array("label" => "返送伝票番号", "form" => $html);
	}

	private function getSlipNumber($param){
		return (!is_array($param) && is_string($param) && strlen($param)) ? $param : null;
	}
}
SOYShopPlugin::extension("soyshop.order.search", "returns_slip_number", "ReturnsSlipNumberSearch");
