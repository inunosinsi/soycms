<?php

class PaymentCustomUtil {

	public static function getConfig(){
    	return SOYShop_DataSets::get("payment_custom", array(
    		"name" => "",
    		"description" => "",
    		"mail" => "支払方法：***",
    		"price" => 0,
    		"status" => "2"
    	));
    }

	public static function saveConfig($values){
		SOYShop_DataSets::put("payment_custom", $values);
	}
}
