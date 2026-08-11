<?php

class AddPaymentStatus extends SOYShopOrderStatus{

	function paymentStatusItem(){
		SOY2::import("module.plugins.add_payment_status.util.AddPaymentStatusUtil");
		$config = AddPaymentStatusUtil::getConfig();
		if(count($config)){
			$list = array();
			foreach($config as $key => $conf){
				$list[$key] = array("label" => $conf, "mail" => null);
			}
			return $list;
		}
	}
}
SOYShopPlugin::extension("soyshop.order.status", "add_payment_status", "AddPaymentStatus");
