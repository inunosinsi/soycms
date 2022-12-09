<?php

class FixedPointGrantUtil {

	const PLUGIN_ID = "fixed_point_grant";

	public static function getConfig(){
		return SOYShop_DataSets::get("fixed_point_grant.config", array(
			"fixed_point" => 0,
		));
	}

	public static function saveConfig($values){
		$values["fixed_point"] = soyshop_convert_number($values["fixed_point"], 0);
		SOYShop_DataSets::put("fixed_point_grant.config", $values);
	}
}
