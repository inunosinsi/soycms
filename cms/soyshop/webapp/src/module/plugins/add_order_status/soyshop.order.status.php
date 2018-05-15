<?php

class AddOrderStatus extends SOYShopOrderStatus{

	function statusItem(){
		SOY2::import("module.plugins.add_order_status.util.AddOrderStatusUtil");
		$config = AddOrderStatusUtil::getConfig();
		if(count($config)){
			$list = array();
			foreach($config as $key => $conf){
				$list[$key] = array("label" => $conf, "mail" => null);
			}
		}

		return $list;
	}

}
SOYShopPlugin::extension("soyshop.order.status", "add_order_status", "AddOrderStatus");
