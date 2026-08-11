<?php

class LazyLoadUtil{


	public static function getConfig(){
		return SOYShop_DataSets::get("x_lazy_load.config", array(
			"count" => 3
		));
	}

	public static function saveConfig($values){
		$values["count"] = soyshop_convert_number($values["count"], 3);
		SOYShop_DataSets::put("x_lazy_load.config", $values);
	}
}
