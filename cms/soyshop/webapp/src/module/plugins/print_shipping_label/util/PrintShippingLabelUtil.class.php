<?php

class PrintShippingLabelUtil {
	
	const COMPANY_KURONEKO = "kuroneko";
	
	const TYPE_CONNECT = "connect";
	const TYPE_HATSUBARAI = "hatsubarai";
	const TYPE_TYAKUBARAI = "tyakubarai";
	
	const MODE_CONNECT = "コネクトサービス";
	const MODE_HATSUBARAI = "発払";
	const MODE_TYAKUBARAI = "着払";
	
	public static function getConfig(){
		return SOYShop_DataSets::get("print_shipping_label.config", array(
			"shipping_date" => 0,
			"product" => ""
		));
	}
	
	public static function saveConfig($values){
		$values["shipping_date"] = (isset($values["shipping_date"])) ? (int)$values["shipping_date"] : 0;
		SOYShop_DataSets::put("print_shipping_label.config", $values);
	}
	
	public static function getText($type){
		switch($type){
			case self::TYPE_CONNECT:
				return self::MODE_CONNECT;
			case self::TYPE_TYAKUBARAI:
				return self::MODE_TYAKUBARAI;
			default:
				return self::MODE_HATSUBARAI;
		}
	}
}