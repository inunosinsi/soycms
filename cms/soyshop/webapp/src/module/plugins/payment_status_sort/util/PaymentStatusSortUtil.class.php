<?php

class PaymentStatusSortUtil {

	public static function getConfig(){
		return SOYShop_DataSets::get("payment_status_sort.config", array());
	}

	public static function saveConfig($values){
		SOYShop_DataSets::put("payment_status_sort.config", $values);
	}
}
