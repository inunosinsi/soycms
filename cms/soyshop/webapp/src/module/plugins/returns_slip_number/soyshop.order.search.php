<?php

class ReturnsSlipNumberSearch extends SOYShopOrderSearch{

	function setParameter(array $params){
		$param = SOYShopPluginUtil::convertArray2String($params);
		$params = SOYShopPluginUtil::devideKeywords($param);
		if(!count($params)) return array();

		$binds = array();
		$q = array();
		$i = 0;
		foreach($params as $param){
			$param = trim($param);
			$q[] = "slip_number LIKE :r_slip" . $i;
			$binds[":r_slip" . $i++] = "%" . $param . "%";
		}
		if(!count($q)) return array();

		return array(
			"queries" => array("id IN (SELECT order_id FROM soyshop_returns_slip_number WHERE " . implode(" OR ", $q) . ")"),
			"binds" => $binds
		);
	}

	function searchItems(array $params){
		return array(
			"label" => "返送伝票番号",
			"form" => "<input type=\"text\" name=\"search[customs][returns_slip_number]\" value=\"" . SOYShopPluginUtil::convertArray2String($params) . "\" placeholder=\"スペース区切りで複数ワードで検索できます。\" style=\"width:95%;\">"
		);
	}
}
SOYShopPlugin::extension("soyshop.order.search", "returns_slip_number", "ReturnsSlipNumberSearch");
