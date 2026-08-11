<?php

class ArrivalNewOrderUtil {

	const ON = 1;
	const OFF = 0;

	public static function getConfig(){
		SOY2::import("domain.order.SOYShop_Order");
		return SOYShop_DataSets::get("arrival_new_order.config", array(
			"error" => array(
				SOYShop_Order::PAYMENT_STATUS_WAIT => self::ON,
				SOYShop_Order::PAYMENT_STATUS_CONFIRMED => self::ON
			)
		));
	}

	public static function saveConfig(array $values){
		SOY2::import("domain.order.SOYShop_Order");
		if(!isset($values["error"][SOYShop_Order::PAYMENT_STATUS_WAIT])) $values["error"][SOYShop_Order::PAYMENT_STATUS_WAIT] = self::OFF;
		if(!isset($values["error"][SOYShop_Order::PAYMENT_STATUS_CONFIRMED])) $values["error"][SOYShop_Order::PAYMENT_STATUS_CONFIRMED] = self::OFF;
		SOYShop_DataSets::put("arrival_new_order.config", $values);
	}
}
