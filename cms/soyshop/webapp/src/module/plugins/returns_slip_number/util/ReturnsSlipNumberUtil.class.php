<?php

class ReturnsSlipNumberUtil {

	const PLUGIN_ID = "returns_slip_number_plugin";

	public static function getConfig(){
		return SOYShop_DataSets::get(self::PLUGIN_ID . ".config", array(
			"content" => "返送伝票番号:#RETURNS_SLIP_NUMBER#"
		));
	}

	public static function saveConfig($values){
		return SOYShop_DataSets::put(self::PLUGIN_ID . ".config", $values);
	}
}
