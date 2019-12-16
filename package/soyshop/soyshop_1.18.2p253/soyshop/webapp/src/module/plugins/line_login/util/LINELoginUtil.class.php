<?php

class LINELoginUtil {

	public static function getConfig(){
		return SOYShop_DataSets::get("line_login.config", array());
	}

	public static function saveConfig($values){
		return SOYShop_DataSets::put("line_login.config", $values);
	}
}
