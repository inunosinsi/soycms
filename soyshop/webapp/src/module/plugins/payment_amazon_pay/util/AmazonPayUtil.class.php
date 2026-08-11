<?php

class AmazonPayUtil {

	const REDIRECT_PARAM = "select";
	const BACK_PARAM = "back";

	public static function getConfig($all=true){
		return self::_getConfig($all);
	}

	private static function _getConfig($all=true){
		$cnf = SOYShop_DataSets::get("payment_amazon_pay.config", array(
			"sandbox" => 0,
			"test" => array(
				"merchant_id" => "",
				"access_key_id" => "",
				"secret_access_key" => "",
				"client_id" => "",
				"client_secret" => ""
			),
			"production" => array(
				"merchant_id" => "",
				"access_key_id" => "",
				"secret_access_key" => "",
				"client_id" => "",
				"client_secret"
			)
		));

		if($all) return $cnf;

		$mode = (!isset($cnf["sandbox"]) || (int)$cnf["sandbox"] == 0) ? "production" : "test";
		if(isset($cnf[$mode])){
			$conf = $cnf[$mode];
			$conf["sandbox"] = ($mode == "test");
			return $conf;
		}else{
			return array("merchant_id" => "", "client_id" => "");
		}
	}

	public static function saveConfig($values){
		SOYShop_DataSets::put("payment_amazon_pay.config", $values);
	}

	public static function getRedirectUrl(){
		return soyshop_get_cart_url(false, true) . "?" . AmazonPayUtil::REDIRECT_PARAM;
	}

	public static function getBackUrl(){
		return soyshop_get_cart_url(false, true) . "?" . AmazonPayUtil::BACK_PARAM;
	}

	public static function getActionUrl(){
		return soyshop_get_cart_url(false, true);
	}
}
