<?php
class DeliveryNormalUtil{

	public static function getFreePrice(){
		return SOYShop_DataSets::get("delivery.default.free_price", array(
			"free" => null
		));
	}
	
	public static function saveFreePrice($values){
		$values["free"] = mb_convert_kana($values["free"], "a");
		$values["free"] = (is_numeric($values["free"])) ? $values["free"] : null;
		SOYShop_DataSets::put("delivery.default.free_price", $values);
	}

	public static function getPrice(){
		return SOYShop_DataSets::get("delivery.default.prices", array());
	}
	
	public static function savePrice($values){
		SOYShop_DataSets::put("delivery.default.prices", $values);
	}

	public static function getUseDeliveryTimeConfig(){
		return SOYShop_DataSets::get("delivery.default.use.time", array(
			"use" => 1
		));
	}
	
	public static function saveUseDeliveryTimeConfig($values){
		SOYShop_DataSets::put("delivery.default.use.time", $values);
	}

	public static function getDeliveryTimeConfig(){
		return SOYShop_DataSets::get("delivery.default.delivery_time_config", array(
			"希望なし", "午前中", "12時～14時", "14時～16時", "16時～18時", "18時～20時", "20時〜21時"
		));
	}

	public static function saveDeliveryTimeConfig($values){
		$config = array_diff($values, array(""));
		SOYShop_DataSets::put("delivery.default.delivery_time_config", $config);
	}
	
	public static function getDeliveryDateConfig(){
		return SOYShop_DataSets::get("delivery.default.delivery_date.config", array(
			"use_delivery_date" => 0,
			"use_delivery_date_unspecified" => 1,
			"delivery_shortest_date" => 2,
			"use_re_calc_shortest_date" => 1,
			"delivery_date_period" => 7,
			"delivery_date_format" => "Y年m月d日(#w#)"
		));
	}
	
	public static function saveDeliveryDateConfig($values){
		$values["use_delivery_date"] = (isset($values["use_delivery_date"])) ? (int)$values["use_delivery_date"] : 0;
		$values["use_delivery_date_unspecified"] = (isset($values["use_delivery_date_unspecified"])) ? (int)$values["use_delivery_date_unspecified"] : 0;
		SOYShop_DataSets::put("delivery.default.delivery_date.config", $values);
	}

	public static function getTitle(){
		return SOYShop_DataSets::get("delivery.default.title", "宅配便");
	}
	
	public static function saveTitle($value){
		SOYShop_DataSets::put("delivery.default.title", $value);
	}

	public static function getDescription(){
		return SOYShop_DataSets::get("delivery.default.description", "宅配便で配送します。");
	}
	
	public static function saveDescription($value){
		SOYShop_DataSets::put("delivery.default.description", $value);
	}
}