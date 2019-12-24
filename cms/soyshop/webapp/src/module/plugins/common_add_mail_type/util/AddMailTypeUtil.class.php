<?php

class AddMailTypeUtil {

	public static function getConfig(){
		return SOYShop_DataSets::get("add_mail_type.config", array());
	}

	public static function saveConfig($values){
		SOYShop_DataSets::put("add_mail_type.config", $values);
	}
}
