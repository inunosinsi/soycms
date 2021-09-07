<?php

class AutoCompletionUtil {

	const FIELD_ID = "auto_completion_item_name";

	public static function getConfig(){
		return SOYShop_DataSets::get(self::FIELD_ID . ".config", array(
			"count" => 10	//ヒット件数
		));
	}

	public static function saveConfig($values){
		if(!isset($values["count"]) || !is_numeric($values["count"])) $values["count"] = 10;
		SOYShop_DataSets::put(self::FIELD_ID . ".config", $values);
	}
}
