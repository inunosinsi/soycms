<?php
class TakeOverCustomerUtil {

	public static function getConfig(){
		return SOYShop_DataSets::get("take_over_customer_info.config", array(
			"shopId" => null
		));
	}

	public static function saveConfig($values){
		$values["shopId"] = (isset($values["shopId"]) && is_numeric($values["shopId"])) ? (int)$values["shopId"] : null;
		SOYShop_DataSets::put("take_over_customer_info.config", $values);
	}
}
