<?php

class OrderStatusSortUtil {

	public static function getConfig(){
		return SOYShop_DataSets::get("order_status_sort.config", array());
	}

	public static function saveConfig($values){
		SOYShop_DataSets::put("order_status_sort.config", $values);
	}
}
