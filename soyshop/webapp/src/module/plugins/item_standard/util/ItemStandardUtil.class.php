<?php
SOY2DAOFactory::importEntity("SOYShop_DataSets");

class ItemStandardUtil{

	const FIELD_ID_PREFIX = "item_standard_plugin_";

	public static function getConfig(){
		return SOYShop_DataSets::get("item_standard.config", array());
	}

	public static function saveConfig($values){
		SOYShop_DataSets::put("item_standard.config", $values);
	}

	/**
	 * どこかのバージョンでFIELD_IDからitem_standard_plugin_を除いてしまったからその対策
	 * @param int, string
	 * @return string
	 */
	public static function getStandardValueByItemId(int $itemId, string $fieldId){
		$v = "";
		$res = strpos($fieldId, self::FIELD_ID_PREFIX);
		if(!is_numeric($res) || $res > 0) $v = soyshop_get_item_attribute_value($itemId, self::FIELD_ID_PREFIX . $fieldId, "string");
		return (strlen($v)) ? $v : soyshop_get_item_attribute_value($itemId, $fieldId, "string");
	}
}
