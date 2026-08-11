<?php

class FacebookLoginUtil {

	const FIELD_ID = "social_login_facebook_login";

	public static function getConfig(){
		return SOYShop_DataSets::get("facebook_login.config", array());
	}

	public static function saveConfig($values){
		return SOYShop_DataSets::put("facebook_login.config", $values);
	}
}
