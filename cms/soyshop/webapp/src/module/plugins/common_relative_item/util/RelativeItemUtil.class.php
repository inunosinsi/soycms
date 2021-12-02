<?php

class RelativeItemUtil{

	const FIELD_ID = "_relative_items";

	public static function getConfig(){
		return SOYShop_DataSets::get("relative_item.config", array(
			"defaultSort" => "name",
			"isReverse" => 0
		));
	}

	public static function saveConfig(array $values){
		SOYShop_DataSets::put("relative_item.config", $values);
	}

	public static function getCodesByItemId(int $itemId){
		$v = soyshop_get_item_attribute_value($itemId, self::FIELD_ID, "string");
		if(!strlen($v)) return array();

		$codes = soy2_unserialize($v);
		return (is_array($codes)) ? $codes : array();
	}

	public static function save(int $itemId, array $arr){
		$v = (count($arr)) ? soy2_serialize($arr) : "";
		$attr = soyshop_get_item_attribute_object($itemId, self::FIELD_ID);
		$attr->setValue($v);
		soyshop_save_item_attribute_object($attr);
	}
}
