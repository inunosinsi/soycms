<?php

class ReRegiserUtil {

	public static function getConfig(){
		return SOYShop_DataSets::get("order_re_register.config", array(
			"customer" => 0
		));
	}

	public static function saveConfig($values){
		$values["customer"] = (isset($values["customer"])) ? $values["customer"] : 0;
		SOYShop_DataSets::put("order_re_register.config", $values);
	}
}
