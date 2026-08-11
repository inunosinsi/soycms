<?php

class CustomfieldUtil {

	const GLOBAL_INDEX = "customfield_acceleration_key";

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

	/**
	 * 指定の記事ID一覧分のカスタムフィールドの値を取得してグローバル変数に格納しておく
	 * @param array itemIds, array fieldIds
	 */
	public static function setValuesByEntryIdsAndFieldIds(array $itemIds, array $fieldIds){
		if(!count($itemIds)) return;
		if(!isset($GLOBALS[self::GLOBAL_INDEX])) $GLOBALS[self::GLOBAL_INDEX] = array();

		if(count($fieldIds)){
			try{
				$res = soycms_get_hash_table_dao("item_attribute")->executeQuery(
					"SELECT item_id, item_field_id, item_value, item_extra_values ".
					"FROM soyshop_item_attribute ".
					"WHERE item_id IN (" . implode(",", $itemIds) . ") ".
					"AND item_field_id IN (\"" . implode("\",\"", $fieldIds) . "\")"
				);
			}catch(Exception $e){
				$res = array();
			}

			if(count($res)){
				foreach($res as $v){
					if(!isset($v["item_id"]) || !is_numeric($v["item_id"])) continue;
					$itemId = (int)$v["item_id"];
					if(!isset($GLOBALS[self::GLOBAL_INDEX][$itemId])) $GLOBALS[self::GLOBAL_INDEX][$itemId] = array();
					$extra = (isset($v["item_extra_values"]) && is_string($v["item_extra_values"]) && strlen($v["item_extra_values"])) ? soy2_unserialize($v["item_extra_values"]) : null;
					$GLOBALS[self::GLOBAL_INDEX][$itemId][$v["item_field_id"]] = array("value" => $v["item_value"], "extraValues" => $extra);
				}
			}
		}
		
		//値が取得できなかったもの
		foreach($itemIds as $itemId){
			if(!isset($GLOBALS[self::GLOBAL_INDEX][$itemId])) $GLOBALS[self::GLOBAL_INDEX][$itemId] = array();
		}
	}
}
