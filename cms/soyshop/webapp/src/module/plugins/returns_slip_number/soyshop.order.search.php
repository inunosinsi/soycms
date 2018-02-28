<?php

class ReturnsSlipNumberSearch extends SOYShopOrderSearch{

	function setParameter($params){
		$params = self::getParameters($params);
		if(count($params)){
			$queries = array();
			$binds = array();
			$i = 0;
			foreach($params as $param){
				$param = trim($param);
				if(strlen($param)){
					$queries[] = "id IN (SELECT order_id FROM soyshop_order_attribute WHERE order_field_id = 'returns_slip_number_plugin' AND order_value1 LIKE :ReturnesSlipNumber" . $i . ")";
					$binds[":ReturnesSlipNumber" . $i] = "%" . $param . "%";
					$i++;
				}
			}
			if(count($queries)) return array("queries" => $queries, "binds" => $binds);
		}
	}

	function searchItems($params){
		$html = "<input type=\"text\" name=\"search[customs][returns_slip_number]\" value=\"" . self::getParameter($params) . "\" placeholder=\"スペース区切りで複数ワードで検索できます。\" style=\"width:95%;\">";
		return array("label" => "返送伝票番号", "form" => $html);
	}

	private function getParameter($param){
		return (!is_array($param) && is_string($param) && strlen($param)) ? $param : null;
	}

	private function getParameters($param){
		$str = str_replace("　", " ", self::getParameter($param));
		return (strlen($str)) ? explode(" ", $str) : array();
	}
}
SOYShopPlugin::extension("soyshop.order.search", "returns_slip_number", "ReturnsSlipNumberSearch");
