<?php

class AddAuthorSearchColumnSearch extends SOYShopOrderSearch{

	function setParameter(array $params){
		$param = SOYShopPluginUtil::convertArray2String($params);
		if(strlen($param)){
			$q = "id IN (SELECT order_id FROM soyshop_order_state_history WHERE author LIKE :scp_author AND (content LIKE '%管理画面から注文%' OR content LIKE '%注文を受け付けました%'))";
			$binds[":scp_author"] = "%" . htmlspecialchars($param, ENT_QUOTES, "UTF-8") . "%";
			return array("queries" => array($q), "binds" => $binds);
		}
	}

	function searchItems(array $params){
		return array(
			"label" => "注文時の対応者ID",
			"form" => "<input type=\"text\" name=\"search[customs][add_author_search_column]\" value=\"" . SOYShopPluginUtil::convertArray2String($params). "\">"
		);
	}
}
SOYShopPlugin::extension("soyshop.order.search", "add_author_search_column", "AddAuthorSearchColumnSearch");
