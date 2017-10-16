<?php

class GoogleSignInUtil {

	public static function getConfig(){
		return SOYShop_DataSets::get("google_sign_in.config", array());
	}

	public static function saveConfig($values){
		return SOYShop_DataSets::put("google_sign_in.config", $values);
	}
}
