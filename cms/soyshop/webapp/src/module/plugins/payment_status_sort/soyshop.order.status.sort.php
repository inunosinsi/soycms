<?php
/*
 */
class PaymentStatusSort extends SOYShopOrderStatusSort{

	function paymentStatusSort(){
		SOY2::import("module.plugins.payment_status_sort.util.PaymentStatusSortUtil");
		$config = PaymentStatusSortUtil::getConfig();
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
SOYShopPlugin::extension("soyshop.order.status.sort", "payment_status_sort", "PaymentStatusSort");
