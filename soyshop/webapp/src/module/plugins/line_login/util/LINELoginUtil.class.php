<?php

class LINELoginUtil {

	const FIELD_ID = "social_login_line_login";

	public static function getConfig(){
		return SOYShop_DataSets::get("line_login.config", array());
	}

	public static function saveConfig($values){
		return SOYShop_DataSets::put("line_login.config", $values);
	}
}
