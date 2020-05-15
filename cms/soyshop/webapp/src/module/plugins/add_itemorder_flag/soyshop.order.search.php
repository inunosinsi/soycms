<?php

class AddItemOrderFlagSearch extends SOYShopOrderSearch{

	function setParameter($param){
		$param = self::_getParameter($param);
		if(is_string($param)){
			$q = "id IN (SELECT order_id FROM soyshop_orders WHERE flag = :flag)";
			return array("queries" => array($q), "binds" => array(":flag" => $param));
		}
	}

	function searchItems($params){
		SOY2::import("module.plugins.add_itemorder_flag.util.AddItemOrderFlagUtil");
		$config = AddItemOrderFlagUtil::getConfig();
		if(!is_array($config) || !count($config)) return "";

		$param = self::_getParameter($params);

		$html = array();
		$html[] = "<select name=\"search[customs][add_itemorder_flag]\">";
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
		return array("label" => "商品毎のフラグ", "form" => implode("\n", $html));
	}

	private function _getParameter($param){
		return (!is_array($param) && is_string($param) && strlen($param)) ? $param : null;
	}
}
SOYShopPlugin::extension("soyshop.order.search", "add_itemorder_flag", "AddItemOrderFlagSearch");
