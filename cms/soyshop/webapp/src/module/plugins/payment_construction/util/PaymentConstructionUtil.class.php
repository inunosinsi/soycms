<?php

class PaymentConstructionUtil {

	public static function getConfig(){
		return SOYShop_DataSets::get("payment_construction.config", array(
			"items" => "人件費"
		));
	}

	public static function saveConfig($values){
		SOYShop_DataSets::put("payment_construction.config", $values);
	}

	public static function getCommissionItemList(){
		$config = SOYShop_DataSets::get("payment_construction.config", array());
		if(!isset($config["items"]) || !strlen($config["items"])) return array();
		return explode("\n", $config["items"]);
	}
}
