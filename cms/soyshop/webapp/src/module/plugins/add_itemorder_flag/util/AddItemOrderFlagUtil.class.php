<?php

class AddItemOrderFlagUtil {

	public static function getConfig(){
		$config = SOYShop_DataSets::get("add_itemorder_flag.config", array());
		if(!count($config)) return array();

		ksort($config);
		return $config;
	}

	public static function saveConfig($values){
		SOYShop_DataSets::put("add_itemorder_flag.config", $values);
	}
}
