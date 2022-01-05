<?php

class CustomfieldUtil {

	public static function getFieldValues(int $itemId){
		static $list, $fieldIds;
		if(is_null($list)) $list = array();
		if(is_null($fieldIds)) {
			SOY2::import("domain.shop.SOYShop_ItemAttribute");
			$fieldIds = array_keys(SOYShop_ItemAttributeConfig::load(true));
		}

		if(isset($list[$itemId])) return $list[$itemId];
		$list[$itemId] = soyshop_get_hash_table_dao("item_attribute")->getByItemIdAndFieldIds($itemId, $fieldIds, true);
		return $list[$itemId];
	}
}
