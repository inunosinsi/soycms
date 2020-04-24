<?php

class TrackingMoreUtil {

	public static function getConfig(){
		return SOYShop_DataSets::get("tracking_more.config", array(
			"key" => "",
			"try" => 20,
			"start" => 1,
			"end" => 3
		));
	}

	public static function saveConfig($values){
		SOYShop_DataSets::put("tracking_more.config", $values);
	}
}
