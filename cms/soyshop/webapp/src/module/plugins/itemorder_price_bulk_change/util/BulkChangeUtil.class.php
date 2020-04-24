<?php

class BulkChangeUtil {

	const MODE_UP = "up";
	const MODE_DOWN = "down";

	const METHOD_FLOOR = "floor";
	const METHOD_CEIL = "ceil";
	const METHOD_ROUND = "round";

	public static function getConfig(){
		return SOYShop_DataSets::get("itemorder_price_bulk_change.config", array(
			"mode" => "up",
			"method" => "ceil"
		));
	}

	public static function saveConfig($values){
		SOYShop_DataSets::put("itemorder_price_bulk_change.config", $values);
	}

	public static function getModeList(){
		return array(
			self::MODE_UP,
			self::MODE_DOWN
		);
	}

	public static function getModeText($mode){
		switch($mode){
			case self::MODE_UP:
				return "増額";
			case self::MODE_DOWN:
				return "減額";
		}
	}

	public static function getMethodList(){
		return array(
			self::METHOD_FLOOR,
			self::METHOD_CEIL,
			self::METHOD_ROUND
		);
	}

	public static function getMethodText($method){
		switch($method){
			case self::METHOD_CEIL:
				return "切り上げ";
			case self::METHOD_FLOOR:
				return "切り捨て";
			case self::METHOD_ROUND:
				return "四捨五入";
		}
	}
}
