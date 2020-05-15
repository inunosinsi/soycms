<?php

class AddItemOrderStatusSearch extends SOYShopOrderSearch{

	function setParameter($param){
		$param = self::_getParameter($param);
		if(is_string($param)){
			$q = "id IN (SELECT order_id FROM soyshop_orders WHERE status = :status)";
			return array("queries" => array($q), "binds" => array(":status" => $param));
		}
	}

	function searchItems($params){
		SOY2::import("module.plugins.add_itemorder_status.util.AddItemOrderStatusUtil");
		$config = AddItemOrderStatusUtil::getConfig();
		if(!is_array($config) || !count($config)) return "";

		$param = self::_getParameter($params);

		$html = array();
		$html[] = "<select name=\"search[customs][add_itemorder_status]\">";
		$html[] = "<option></option>";
		foreach($config as $idx => $label){
			if($idx == $param){
				$html[] = "<option value=\"" . $idx . "\" selected=\"selected\">" . $label . "</option>";
			}else{
				$html[] = "<option value=\"" . $idx . "\">" . $label . "</option>";
			}
		}
		$html[] = "</select>";
		$html[] = "に設定された商品を含む注文";
		return array("label" => "商品毎の状態", "form" => implode("\n", $html));
	}

	private function _getParameter($param){
		return (!is_array($param) && is_string($param) && strlen($param)) ? $param : null;
	}
}
SOYShopPlugin::extension("soyshop.order.search", "add_itemorder_status", "AddItemOrderStatusSearch");
