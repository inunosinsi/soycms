<?php

class RefundManagerOrderSearch extends SOYShopOrderSearch{

	function setParameter(array $params){
		SOY2::import("module.plugins.refund_manager.util.RefundManagerUtil");
		$where = array();
		if(in_array(0, $params)){
			$where[] = "order_value2 IS NULL";
		}

		if(in_array(1, $params)){
			$where[] = "order_value2 = 1";
		}

		if(count($where)){
			$query = "id IN (SELECT order_id FROM soyshop_order_attribute WHERE order_field_id = '" . RefundManagerUtil::FIELD_ID . "' AND (" . implode(" OR ", $where) . "))";
			return array("queries" => array($query), "binds" => array());
		}
	}

	function searchItems(array $params){
		$html = array();
		if(in_array(0, $params)){
			$html[] = "<label><input type=\"checkbox\" name=\"search[customs][refund_manager][]\" value=\"0\" checked=\"checked\">未処理</label>";
		}else{
			$html[] = "<label><input type=\"checkbox\" name=\"search[customs][refund_manager][]\" value=\"0\">未処理</label>";
		}

		if(in_array(1, $params)){
			$html[] = "<label><input type=\"checkbox\" name=\"search[customs][refund_manager][]\" value=\"1\" checked=\"checked\">処理済み</label>";
		}else{
			$html[] = "<label><input type=\"checkbox\" name=\"search[customs][refund_manager][]\" value=\"1\">処理済み</label>";
		}


		return array(
			"label" => "返金関連",
			"form" => implode("", $html)
		);
	}
}
SOYShopPlugin::extension("soyshop.order.search", "refund_manager", "RefundManagerOrderSearch");
