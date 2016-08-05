<?php

class PointGrantUtil {

    public static function getConfig(){
		return SOYShop_DataSets::get("point_grant_config", array(
			"sale_point_double_on" => 0,
			"sale_point_double" => 1,	//倍率,
			"point_birthday_present" => 0
		));
	}
	
	public static function saveConfig($values){
		$values["sale_point_double_on"] = soyshop_convert_number($values["sale_point_double_on"], 0);
		SOYShop_DataSets::put("point_grant_config", $values);
	}
}
?>