<?php

class SlipNumberUtil {

	const PLUGIN_ID = "slip_number_plugin";

	public static function getConfig(){
		return SOYShop_DataSets::get(self::PLUGIN_ID . ".config", array(
			"content" => "伝票番号:#SLIP_NUMBER#"
		));
	}

	public static function saveConfig($values){
		return SOYShop_DataSets::put(self::PLUGIN_ID . ".config", $values);
	}

	public static function checkIsPon($line){
		$values = explode(",", $line);
		$h = self::_getPonHeader();
		foreach($values as $i => $v){
			if(!isset($h[$i]) || $h[$i] != trim($v)) return false;
		}
		return true;
	}

	private static function _getPonHeader(){
		return array("No", "Delivery", "STATUS", "ProcFlg");
	}
}
