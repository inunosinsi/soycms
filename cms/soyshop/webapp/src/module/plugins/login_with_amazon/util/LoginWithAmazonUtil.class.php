<?php

class LoginWithAmazonUtil {

	public static function getConfig(){
		return SOYShop_DataSets::get("login_with_amazon.config", array());
	}

	public static function saveConfig($values){
		return SOYShop_DataSets::put("login_with_amazon.config", $values);
	}
}
