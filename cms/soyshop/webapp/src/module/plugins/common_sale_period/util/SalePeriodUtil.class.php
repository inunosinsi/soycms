<?php

class SalePeriodUtil{
	
	public static function getMailTypes(){
		return array("end");
	}
	
	public static function getConfig(){
		return SOYShop_DataSets::get("sale_period.config", array(
			"end" => 5		//セール終了〇日前
		));
	}
	
	public static function saveConfig($values){
		$values["end"] = soyshop_convert_number($values["end"], 0);
		SOYShop_DataSets::put("sale_period.config", $values);
	}
	
	public static function getMailConfig($type = "end"){
		return SOYShop_DataSets::get("sale_period.mail." . $type, array(
			"title" => "#ITEM_NAME#のセール関連のお知らせ",
			"content" => "#ITEM_NAME#のセール販売は#SALE_END#までです"
		));
	}
	
	public static function saveMailConfig($values, $type = "end"){
		SOYShop_DataSets::put("sale_period.mail." . $type, $values);
	}
}
?>