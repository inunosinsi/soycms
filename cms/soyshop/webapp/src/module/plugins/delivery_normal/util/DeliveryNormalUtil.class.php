<?php
class DeliveryNormalUtil{

	public static function getFreePrice(){
		return SOYShop_DataSets::get("delivery.default.free_price", array(
			"free" => null
		));
	}

	public static function getPrice(){
		return SOYShop_DataSets::get("delivery.default.prices", array());
	}

	public static function getUseDeliveryTimeConfig(){
		return SOYShop_DataSets::get("delivery.default.use.time", array(
			"use" => 1
		));
	}

	public static function getDeliveryTimeConfig(){
		return SOYShop_DataSets::get("delivery.default.delivery_time_config", array(
			"希望なし", "午前中", "12時～14時", "14時～16時", "16時～18時", "18時～21時"
		));
	}

	public static function getTitle(){
		return SOYShop_DataSets::get("delivery.default.title", "宅配便");
	}

	public static function getDescription(){
		return SOYShop_DataSets::get("delivery.default.description", "宅配便で配送します。");
	}

}