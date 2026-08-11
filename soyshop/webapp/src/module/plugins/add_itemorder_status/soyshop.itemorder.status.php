<?php

class AddItemOrderStatus extends SOYShopItemOrderStatus{

	function statusItem(){
		SOY2::import("module.plugins.add_itemorder_status.util.AddItemOrderStatusUtil");
		$config = AddItemOrderStatusUtil::getConfig();
		if(count($config)){
			$list = array();
			foreach($config as $key => $label){
				$list[$key] = $label;
			}
			return $list;
		}
	}
}
SOYShopPlugin::extension("soyshop.itemorder.status", "add_itemorder_status", "AddItemOrderStatus");
