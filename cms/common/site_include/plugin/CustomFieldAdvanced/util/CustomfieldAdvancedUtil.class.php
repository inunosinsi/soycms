<?php

class CustomfieldAdvancedUtil {

	const GLOBAL_INDEX = "customfield_advanced_acceleration_key";

	public static function createHash(string $v){
		return substr(md5($v), 0, 6);
	}

	//カスタムフィールドアドバンスドの設定内に記事フィールドはあるか？
	public static function checkIsEntryField(array $fields){
		static $isEntry;
		if(is_null($isEntry)){
			$isEntry = false;
			if(is_array($fields) && count($fields)){
				foreach($fields as $field){
					if($field->getType() == "entry"){
						$isEntry = true;
						break;
					}
				}
			}
		}
		return $isEntry;
	}

	public static function checkIsLabelField(array $fields){
		static $isLabel;
		if(is_null($isLabel)){
			$isLabel = false;
			if(is_array($fields) && count($fields)){
				foreach($fields as $field){
					if($field->getType() == "label"){
						$isLabel = true;
						break;
					}
				}
			}
		}
		return $isLabel;
	}

	public static function checkIsListField(array $fields){
		static $isList;
		if(is_null($isList)){
			$isList = false;
			if(is_array($fields) && count($fields)){
				foreach($fields as $field){
					if($field->getType() == "list"){
						$isList = true;
						break;
					}
				}
			}
		}
		return $isList;
	}

	/**
	 * 指定の記事ID一覧分のカスタムフィールドの値を取得してグローバル変数に格納しておく
	 * @param array entryIds, array fieldIds
	 */
	public static function setValuesByEntryIdsAndFieldIds(array $entryIds, array $fieldIds){
		if(!count($entryIds)) return;
		if(!isset($GLOBALS[self::GLOBAL_INDEX])) $GLOBALS[self::GLOBAL_INDEX] = array();

		if(count($fieldIds)){
			try{
				$res = soycms_get_hash_table_dao("entry_attribute")->executeQuery(
					"SELECT entry_id, entry_field_id, entry_value, entry_extra_values ".
					"FROM EntryAttribute ".
					"WHERE entry_id IN (" . implode(",", $entryIds) . ") ".
					"AND entry_field_id IN (\"" . implode("\",\"", $fieldIds) . "\")"
				);
			}catch(Exception $e){
				$res = array();
			}

			if(count($res)){
				foreach($res as $v){
					if(!isset($v["entry_id"]) || !is_numeric($v["entry_id"])) continue;
					$entryId = (int)$v["entry_id"];
					if(!isset($GLOBALS[self::GLOBAL_INDEX][$entryId])) $GLOBALS[self::GLOBAL_INDEX][$entryId] = array();
					$extra = (isset($v["entry_extra_values"]) && is_string($v["entry_extra_values"]) && strlen($v["entry_extra_values"])) ? soy2_unserialize($v["entry_extra_values"]) : null;
					$GLOBALS[self::GLOBAL_INDEX][$entryId][$v["entry_field_id"]] = array("value" => $v["entry_value"], "extraValues" => $extra);
				}
			}
		}
		
		//値が取得できなかったもの
		foreach($entryIds as $entryId){
			if(!isset($GLOBALS[self::GLOBAL_INDEX][$entryId])) $GLOBALS[self::GLOBAL_INDEX][$entryId] = array();
		}
	}

	/**
	 * @param int entryId, array fieldIds
	 * @return array 
	 * array(
	 * 	fieldId => array("value" => "", "extraValues" => "")
	 * 	...
	 * )
	 */
	public static function getValuesByFieldIds(int $entryId, array $fieldIds){
		if(!count($fieldIds)) return array();

		$arr = array();
		
		// setValuesByEntryIdsAndFieldIdsを実行した場合はその結果を格納する
		if(isset($GLOBALS[self::GLOBAL_INDEX][$entryId])){
			$arr = &$GLOBALS[self::GLOBAL_INDEX][$entryId];
		}else{
			try{
				$res = soycms_get_hash_table_dao("entry_attribute")->executeQuery(
					"SELECT entry_field_id, entry_value, entry_extra_values ".
					"FROM EntryAttribute ".
					"WHERE entry_id = :entryId ".
					"AND entry_field_id IN (\"" . implode("\",\"", $fieldIds) . "\")",
					array(
						":entryId" => $entryId
					)
				);
			}catch(Exception $e){
				$res = array();
			}

			if(count($res)){
				foreach($res as $v){
					$extra = (isset($v["entry_extra_values"]) && is_string($v["entry_extra_values"]) && strlen($v["entry_extra_values"])) ? soy2_unserialize($v["entry_extra_values"]) : null;
					$arr[$v["entry_field_id"]] = array("value" => $v["entry_value"], "extraValues" => $extra);
				}
			}
		}

		//　fieldIdに対応した値がない場合は空文字を入れておく
		foreach($fieldIds as $fieldId){
			if(!isset($arr[$fieldId])) $arr[$fieldId] = array("value" => "", "extraValues" => null);
		}
		
		return $arr;
	}

	/**
	 * ブロックに紐づいたラベルIDと高度な設定で設定したラベルIDが同じでなければtrue
	 * @param int labelId, int labelIdWithBlock
	 * @return bool
	 */
	public static function checkLabelConfigOnBlock(int $labelId, int $labelIdWithBlock){
		return ($labelId > 0 && $labelId != $labelIdWithBlock);
	}
}
