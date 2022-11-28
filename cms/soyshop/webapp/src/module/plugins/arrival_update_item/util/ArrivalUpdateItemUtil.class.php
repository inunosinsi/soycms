<?php

class ArrivalUpdateItemUtil {

	public static function getConfig(){
		return SOYShop_DataSets::get("arrival_update_item.config", array(
			"count" => 5,
		));
	}

	public static function saveConfig($values){
		$values["count"] = (isset($values["count"]) && is_numeric($values["count"])) ? (int)$values["count"] : 5;
		SOYShop_DataSets::put("arrival_update_item.config", $values);
	}
}
