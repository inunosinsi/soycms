<?php

class AttributeOrderCountUtil {
	
	public static function getConfig(){
		return SOYShop_DataSets::get("attribute_order_count.config", array());
	}
	
	public static function saveConfig($values){
		SOYShop_DataSets::put("attribute_order_count.config", $values);
	}
	
	public static function getAttrConfig(){
		return SOYShop_DataSets::get("attribute_order_count.attr", 1);
	}
	
	public static function saveAttrConfig($checked){
		SOYShop_DataSets::put("attribute_order_count.attr", $checked);
	}
	
	public static function getPeriodConfig(){
		return SOYShop_DataSets::get("attribute_order_count.period", null);
	}
	
	public static function savePeriodConfig($v){
		SOYShop_DataSets::put("attribute_order_count.period", $v);
	}
}
?>