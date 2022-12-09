<?php
/*
 * soyshop.item.csv.php
 * Created: 2010/02/15
 */

class SOYShop_RelativeItem_CSV extends SOYShopItemCSVBase{

	const PLUGIN_ID = "_relative_items";

	function getLabel(){
		return "関連商品";
	}

	/**
	 * export
	 */
	function export($itemId){
		$arr = soy2_unserialize(soyshop_get_item_attribute_value($itemId, self::PLUGIN_ID, "string"));
		return (is_array($arr)) ? implode(" ",$arr) : "";
	}

	/**
	 * import
	 */
	function import($itemId, $value){
		$value = trim($value);
		$v = (strlen($value)) ? soy2_serialize(explode(" ",$value)) : null;

		$attr = soyshop_get_item_attribute_object($itemId, self::PLUGIN_ID);
		$attr->setValue($v);
		soyshop_save_item_attribute_object($attr);
	}
}

SOYShopPlugin::extension("soyshop.item.csv","common_relative_item","SOYShop_RelativeItem_CSV");
//SOYShopPlugin::extension("soyshop.item.csv","common_relative_item2","SOYShop_RelativeItem_CSV");
//SOYShopPlugin::extension("soyshop.item.csv","common_relative_item3","SOYShop_RelativeItem_CSV");
