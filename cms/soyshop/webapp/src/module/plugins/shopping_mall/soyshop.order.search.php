<?php

class ShoppingMallOrderSearch extends SOYShopOrderSearch{

	function setParameter($params){
		if(!SOYMALL_SELLER_ACCOUNT) return array(array(), array());

		$adminId = (int)SOY2ActionSession::getUserSession()->getAttribute("userid");

		SOY2::import("module.plugins.shopping_mall.domain.SOYMall_ItemRelationDAO");
		try{
			$objs = SOY2DAOFactory::create("SOYMall_ItemRelationDAO")->getByAdminId($adminId);
		}catch(Exception $e){
			$objs = array();
		}

		$queries = array();
		$binds = array();
		if(count($objs)){
			$itemIds = array();
			foreach($objs as $obj){
				$itemIds[] = (int)$obj->getItemId();
			}
			$queries[] = "id IN (SELECT order_id FROM soyshop_orders WHERE item_id IN (" . implode(",", $itemIds) . "))";
		}else{	//絶対に検索に引っかからないqueryを指定
			$queries[] = "id IN (SELECT order_id FROM soyshop_orders WHERE item_id = 0)";
		}

		return array("queries" => $queries, "binds" => $binds);
	}

	function searchItems($params){}
}
SOYShopPlugin::extension("soyshop.order.search", "shopping_mall", "ShoppingMallOrderSearch");
