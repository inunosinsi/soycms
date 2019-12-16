<?php

class PointGrantUtil {

    public static function getConfig(){
		$config = SOYShop_DataSets::get("point_grant_config", array(
			"percentage" => 10,
			"sale_point_double_on" => 0,
			"sale_point_double" => 1,	//倍率,
			"point_birthday_present" => 0
		));

		if(!isset($config["percentage"])){
			SOY2::import("module.plugins.common_point_base.util.PointBaseUtil");
			$baseConfig = PointBaseUtil::getConfig();
			$config["percentage"] = (isset($baseConfig["percentage"])) ? (int)$baseConfig["percentage"] : 0;
			SOYShop_DataSets::put("point_grant_config", $config);
		}

		return $config;
	}

	public static function saveConfig($values){
		$values["percentage"] = soyshop_convert_number($values["percentage"], null);
		$values["sale_point_double_on"] = soyshop_convert_number($values["sale_point_double_on"], 0);
		SOYShop_DataSets::put("point_grant_config", $values);
	}
}
