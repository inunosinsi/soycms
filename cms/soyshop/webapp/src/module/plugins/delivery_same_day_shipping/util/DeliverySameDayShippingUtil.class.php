<?php
class DeliverySameDayShippingUtil{
	
	public static function getConfig(){
		return SOYShop_DataSets::get("delivery_same_day_shopping.config", array(
			"title" => "宅配便",
			"businessHour" => array(
								"start" => array(
									"hour" => "10", 
									"min" => "00"
								),
								"end" => array(
									"hour" => "19",
									"min" => "00"
								)
							),
			"delivery" => array(
								"am" => array(
									"day" => 1,
									"description" => ""
								),
								"pm" => array(
									"day" => 2,
									"description" => ""
								),
								"regular" => array(
									"day" => 1,
									"description" => ""
								),
							),
		));
	}
	
	public static function saveConfig($values){
		SOYShop_DataSets::put("delivery_same_day_shopping.config", $values);
	}

	public static function getFreePrice(){
		return SOYShop_DataSets::get("delivery.default.free_price", array(
			"free" => null
		));
	}

	public static function getPrice(){
		return SOYShop_DataSets::get("delivery.default.prices", array());
	}
}