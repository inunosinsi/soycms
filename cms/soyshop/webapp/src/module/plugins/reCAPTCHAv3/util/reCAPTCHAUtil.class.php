<?php

class reCAPTCHAUtil {

	public static function getConfig(){
		return SOYShop_DataSets::get("reCAPTCHA.config", array(
			"site_key" => "",
			"secret_key" => ""
		));
	}

	public static function saveConfig($values){
		SOYShop_DataSets::put("reCAPTCHA.config", $values);
	}
}
