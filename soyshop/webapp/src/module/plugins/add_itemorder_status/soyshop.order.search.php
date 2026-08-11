<?php

class AddItemOrderStatusSearch extends SOYShopOrderSearch{

	function setParameter(array $params){
		$param = SOYShopPluginUtil::convertArray2String($params);
		if(is_string($param)){
			return array(
				"queries" => array("id IN (SELECT order_id FROM soyshop_orders WHERE status = :status)"),
				"binds" => array(":status" => $param)
			);
		}
	}

	function searchItems(array $params){
		SOY2::import("module.plugins.add_itemorder_status.util.AddItemOrderStatusUtil");
		$cnf = AddItemOrderStatusUtil::getConfig();
		if(!is_array($cnf) || !count($cnf)) return "";

		$param = SOYShopPluginUtil::convertArray2String($params);

		$html = array();
		$html[] = "<select name=\"search[customs][add_itemorder_status]\">";
		$html[] = "<option></option>";
		foreach($cnf as $idx => $label){
			if($idx == $param){
				$html[] = "<option value=\"" . $idx . "\" selected=\"selected\">" . $label . "</option>";
			}else{
				$html[] = "<option value=\"" . $idx . "\">" . $label . "</option>";
			}
		}
		$html[] = "</select>";
		$html[] = "に設定された商品を含む注文";
		return array(
			"label" => "商品毎の状態",
			"form" => implode("\n", $html)
		);
	}
}
SOYShopPlugin::extension("soyshop.order.search", "add_itemorder_status", "AddItemOrderStatusSearch");
