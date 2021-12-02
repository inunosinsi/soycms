<?php

class CommonPointGrantCSV extends SOYShopItemCSVBase{

	const PLUGIN_ID = "common_point_base";

	function getLabel(){
		return "ポイント";
	}

	/**
	 * export
	 * @param integer item_id
	 * @return value
	 */
	function export($itemId){
		return soyshop_get_item_attribute_value($itemId, self::PLUGIN_ID, "int");
	}

	/**
	 * import
	 * void
	 */
	function import($itemId, $point){
		$point = trim($point);
		$point = (is_numeric($point)) ? (int)$point : null;

		$attr = soyshop_get_item_attribute_object($itemId, self::PLUGIN_ID);
		$attr->setValue($point);
		soyshop_save_item_attribute_object($attr);
	}
}

SOYShopPlugin::extension("soyshop.item.csv","common_point_grant","CommonPointGrantCSV");
