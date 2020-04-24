<?php

class UserGoogleMapUtil {

	public static function getConfig(){
		return SOYShop_DataSets::get("user_google_map.config", array());
	}

	public static function saveConfig($values){
		return SOYShop_DataSets::put("user_google_map.config", $values);
	}
}
