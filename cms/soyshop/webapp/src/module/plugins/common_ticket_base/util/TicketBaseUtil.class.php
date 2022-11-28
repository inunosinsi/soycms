<?php

class TicketBaseUtil {

	const PLUGIN_ID = "common_ticket_base";

	public static function getConfig(){
		return SOYShop_DataSets::get(self::PLUGIN_ID . ".config", array(
			"label" => "チケット",
			"unit" => "枚"
		));
	}

	public static function saveConfig($values){
		foreach(array("label" => "チケット", "unit" => "枚") as $key => $v){
			$values[$key] = (strlen($values[$key])) ? htmlspecialchars($values[$key], ENT_QUOTES, "UTF-8") : $v;
		}
		SOYShop_DataSets::put(self::PLUGIN_ID . ".config", $values);
	}
}
