<?php

class AddOrderStatusUtil {

	public static function getConfig(){
		$config = SOYShop_DataSets::get("add_order_status.config", array());
		if(!count($config)) return array();
		
		ksort($config);
		return $config;
	}

	public static function saveConfig($values){
		SOYShop_DataSets::put("add_order_status.config", $values);
	}
}
