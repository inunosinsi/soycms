<?php

class ConsumptionTaxUtil{

	const METHOD_FLOOR = 0;	//端数を切り捨て
	const METHOD_ROUND = 1;	//端数を四捨五入
	const METHOD_CEIL = 2;	//端数を切り上げ

	const FIELD_REDUCED_TAX_RATE = "reduced_tax_rate";

	function __construct(){
		SOY2DAOFactory::importEntity("SOYShop_DataSets");
	}

	public static function getConfig(){
		return self::_getConfig();
	}

	private static function _getConfig(){
		return SOYShop_DataSets::get("consumption_tax.config", array(
			"method" => 0,
			"reduced_tax_rate" => 0,
			"reduced_tax_rate_start_date" => "",
		));
	}

	public static function saveConfig($values){
		SOYShop_DataSets::put("consumption_tax.config", $values);
	}

	public static function saveReducedTaxRateItem(bool $on, int $itemId){
		$v = ($on) ? 1 : null;
		$attr = soyshop_get_item_attribute_object($itemId, self::FIELD_REDUCED_TAX_RATE);
		$attr->setValue($v);
		soyshop_save_item_attribute_object($attr);
	}
}
