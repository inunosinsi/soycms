<?php

class ThumbnailPluginUtil {

	const GLOBAL_INDEX = "soycms_thumbnail_acceleration_key";

	const PREFIX_IMAGE = "soycms_thumbnail_plugin_";

	const UPLOAD_IMAGE = "soycms_thumbnail_plugin_upload";
	const TRIMMING_IMAGE = "soycms_thumbnail_plugin_trimming";
	const RESIZE_IMAGE = "soycms_thumbnail_plugin_resize";

	const THUMBNAIL_CONFIG = "soycms_thumbnail_plugin_config";
	const THUMBNAIL_ALT = "soycms_thumbnail_plugin_alt";


	private static function _getFieldIds(){
		return array(
			self::UPLOAD_IMAGE,
			self::TRIMMING_IMAGE,
			self::RESIZE_IMAGE,
			self::THUMBNAIL_CONFIG,
			self::THUMBNAIL_ALT
		);
	}

	/**
	 * 指定の記事ID一覧分の画像ファイルのパスを取得してグローバル変数に格納しておく
	 * @param array entryIds
	 */
	public static function setThumbnailPathesByEntryIds(array $entryIds){
		if(!count($entryIds)) return;
		if(!isset($GLOBALS[self::GLOBAL_INDEX])) $GLOBALS[self::GLOBAL_INDEX] = array();

		try{
			$res = soycms_get_hash_table_dao("entry_attribute")->executeQuery(
				"SELECT entry_id, entry_field_id, entry_value ".
				"FROM EntryAttribute ".
				"WHERE entry_id IN (" . implode(",", $entryIds) . ") ".
				"AND entry_field_id IN (\"" . implode("\",\"", self::_getFieldIds()) . "\")"
			);
		}catch(Exception $e){
			$res = array();
		}
		
		if(count($res)){
			foreach($res as $v){
				if(!isset($v["entry_id"]) || !is_numeric($v["entry_id"])) continue;
				$entryId = (int)$v["entry_id"];
				if(!isset($GLOBALS[self::GLOBAL_INDEX][$entryId])) $GLOBALS[self::GLOBAL_INDEX][$entryId] = array();
				$GLOBALS[self::GLOBAL_INDEX][$entryId][$v["entry_field_id"]] = $v["entry_value"];
			}
		}

		//値が取得できなかったもの
		foreach($entryIds as $entryId){
			if(!isset($GLOBALS[self::GLOBAL_INDEX][$entryId])) $GLOBALS[self::GLOBAL_INDEX][$entryId] = array();
		}
	}

	/**
	 * サムネイルプラグインに関係する画像ファイルのパスを取得
	 * @param int
	 * @return array
	 */
	public static function getThumbnailPathesByEntryId(int $entryId){
		if($entryId > 0){
			if(isset($GLOBALS[self::GLOBAL_INDEX][$entryId])){
				$attrValues = &$GLOBALS[self::GLOBAL_INDEX][$entryId];
			}else{
				try{
					$res = soycms_get_hash_table_dao("entry_attribute")->executeQuery(
						"SELECT entry_field_id, entry_value ".
						"FROM EntryAttribute ".
						"WHERE entry_id = :entryId ".
						"AND entry_field_id IN (\"" . implode("\",\"", self::_getFieldIds()) . "\")", 
						array(":entryId" => $entryId)
					);
				}catch(Exception $e){
					$res = array();
				}

				$attrValues = array();
				if(count($res)){
					foreach($res as $v){
						if(!isset($v["entry_value"]) || !is_string($v["entry_value"]) || !strlen($v["entry_value"])) continue;
						$attrValues[$v["entry_field_id"]] = $v["entry_value"];
					}
				}
			}
		}

		foreach(self::_getFieldIds() as $fieldId){
			if(!isset($attrValues[$fieldId])) $attrValues[$fieldId] = "";
		}

		return $attrValues;
	}

	/**
	 * @return SiteConfig
	 */
	public static function getSiteConfig(){
		try{
			return SOY2DAOFactory::create("cms.SiteConfigDAO")->get();
		}catch(Exception $e){
			return new SiteConfig();
		}
	}

	/**
	 * @return string
	 */
	public static function getSiteDirectoryName(){
		return trim(str_replace($_SERVER["DOCUMENT_ROOT"], "", UserInfoUtil::getSiteDirectory()), "/");
	}
}
