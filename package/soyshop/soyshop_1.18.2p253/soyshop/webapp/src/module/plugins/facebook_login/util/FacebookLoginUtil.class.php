<?php

class FacebookLoginUtil {

	public static function getConfig(){
		return SOYShop_DataSets::get("facebook_login.config", array());
	}

	public static function saveConfig($values){
		return SOYShop_DataSets::put("facebook_login.config", $values);
	}
}
