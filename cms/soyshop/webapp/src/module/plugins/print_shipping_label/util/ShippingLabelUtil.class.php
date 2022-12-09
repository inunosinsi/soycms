<?php

class ShippingLabelUtil {
	
	const COMPANY_KURONEKO = "kuroneko";
	
	const LABEL_KURONEKO = "クロネコヤマト";
	
	const TYPE_CORECT = "corect";
	const TYPE_HATSUBARAI = "hatsubarai";
	const TYPE_TYAKUBARAI = "tyakubarai";
	
	const MODE_CORECT = "コレクトサービス";
	const MODE_HATSUBARAI = "発払";
	const MODE_TYAKUBARAI = "着払";
	
	public static function getConfig(){
		return SOYShop_DataSets::get("print_shipping_label.config", array(
			"product" => ""
		));
	}
	
	public static function saveConfig($values){
		$values["shipping_date"] = (isset($values["shipping_date"])) ? (int)$values["shipping_date"] : 0;
		SOYShop_DataSets::put("print_shipping_label.config", $values);
	}
		
	public static function getText($type){
		switch($type){
			case self::TYPE_CORECT:
				return self::MODE_CORECT;
			case self::TYPE_TYAKUBARAI:
				return self::MODE_TYAKUBARAI;
			default:
				return self::MODE_HATSUBARAI;
		}
	}
	
	public static function getCompanyText($comp){
		switch($comp){
			case self::COMPANY_KURONEKO:
			default:
				return self::LABEL_KURONEKO;
		}
	}
}