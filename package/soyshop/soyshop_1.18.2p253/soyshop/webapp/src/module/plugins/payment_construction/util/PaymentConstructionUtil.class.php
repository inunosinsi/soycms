<?php

class PaymentConstructionUtil {

	public static function getConfig(){
		return SOYShop_DataSets::get("payment_construction.config", array(
			"items" => "人件費"
		));
	}

	public static function saveConfig($values){
		$values["display_construction_item"] = (isset($values["display_construction_item"]) && (int)$values["display_construction_item"] === 1) ? 1 : 0;
		SOYShop_DataSets::put("payment_construction.config", $values);
	}

	public static function getCommissionItemList(){
		$config = SOYShop_DataSets::get("payment_construction.config", array());
		if(!isset($config["items"]) || !strlen($config["items"])) return array();
		return explode("\n", $config["items"]);
	}

	public static function getIncludeItemList(){
		$config = SOYShop_DataSets::get("payment_construction.config", array());
		if(!isset($config["items_include"]) || !strlen($config["items_include"])) return array();
		return explode("\n", $config["items_include"]);
	}

	public static function hasConstructionItem(){
		$config = SOYShop_DataSets::get("payment_construction.config", array());
		return (!isset($config["display_construction_item"]) || (int)$config["display_construction_item"] === 1);
	}

	public static function isItemStockAutoInsert(){
		$config = SOYShop_DataSets::get("payment_construction.config", array());
		return (isset($config["item_stock_auto_insert"]) && (int)$config["item_stock_auto_insert"] === 1);
	}
}
