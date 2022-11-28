<?php
/*
 */
class OrderStatusSort extends SOYShopOrderStatusSort{

	function statusSort(){
		SOY2::import("module.plugins.order_status_sort.util.OrderStatusSortUtil");
		$config = OrderStatusSortUtil::getConfig();
		if(!isset($config)) return array();
		if(!is_array($config) || !count($config)) return array();

		asort($config);

		$list = array();	//ステータスコードを順に格納する
		foreach($config as $key => $sort){
			$list[] = $key;
		}
	
		return $list;
	}
}
SOYShopPlugin::extension("soyshop.order.status.sort", "order_status_sort", "OrderStatusSort");
