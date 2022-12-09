<?php

class AttributeOrderTotalUtil {
	
	public static function getConfig(){
		return SOYShop_DataSets::get("attribute_order_total.config", array());
	}
	
	public static function saveConfig($values){
		SOYShop_DataSets::put("attribute_order_total.config", $values);
	}
	
	public static function getAttrConfig(){
		return SOYShop_DataSets::get("attribute_order_total.attr", 1);
	}
	
	public static function saveAttrConfig($checked){
		SOYShop_DataSets::put("attribute_order_total.attr", $checked);
	}
	
	public static function getPeriodConfig(){
		return SOYShop_DataSets::get("attribute_order_total.period", null);
	}
	
	public static function savePeriodConfig($v){
		SOYShop_DataSets::put("attribute_order_total.period", $v);
	}
}
?>