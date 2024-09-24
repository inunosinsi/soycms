<?php

class GeminiAbstractUtil {

	const FIELD_ID = "gemini_abstract";
	const GLOBAL_INDEX = self::FIELD_ID."_list";
	const DATA_SET_KEY = "gemini_abstrat_key";
	const COUNT_OF_CHARS = 250;	// 自動生成する時の文字数

	/**
	 * job中でも利用出来るように$dao->executeQueryでデータを取得する
	 * @return array(pageId => labelId)
	 */
	public static function getBlogPageIds(){
		SOY2::import("domain.cms.Page");
		$dao = new SOY2DAO();

		try{
			$res = $dao->executeQuery(
				"SELECT id, page_config FROM Page ".
				"WHERE page_type = ".Page::PAGE_TYPE_BLOG . " ".
				"AND isPublished = ".Page::PAGE_ACTIVE
			);
		}catch(Exception $e){
			$res = array();
		}
		
		if(!count($res)) return array();

		// 自動生成を有効にするブログページの設定
		$chks = GeminiAbstractUtil::getEnabledBlogPages();
		
		$blogLabelIds = array();
		foreach($res as $v){
			$cnf = soy2_unserialize($v["page_config"]);
			if(!property_exists($cnf, "blogLabelId") || !is_numeric($cnf->blogLabelId) || is_bool(array_search($cnf->blogLabelId, $chks))) continue;
			$blogLabelIds[(int)$v["id"]] = (int)$cnf->blogLabelId;
		}
		
		return $blogLabelIds;
	}

	/**
	 * @param int
	 * @return int
	 */
	public static function getBlogPageIdByEntryId(int $entryId){
		$blogLabelIds = self::getBlogPageIds();
		if(!count($blogLabelIds)) return 0;

		try{
			$entryLabels = soycms_get_hash_table_dao("entry_label")->getByEntryId($entryId);
		}catch(Exception $e){
			$entryLabels = array();
		}
		if(!count($entryLabels)) return 0;

		foreach($entryLabels as $entryLabel){
			$labelId = (int)$entryLabel->getLabelId();

			$blogPageId = array_search($labelId, $blogLabelIds);
			if(is_numeric($blogPageId)) return $blogPageId;
		}

		return 0;
	}

	/**
	 * @param int, int, int
	 * @return string
	 */
	public static function buildPrompt(int $blogPageId, int $entryId, int $count=250){
		$blogPage = soycms_get_hash_table_dao("blog_page")->getById($blogPageId);
		$entry = soycms_get_hash_table_dao("entry")->getById($entryId);
		
		$content = "<h1>".$entry->getTitle()."</h1>";
		$content .= $entry->getContent();
		$content .= $entry->getMore();
		return $content."の内容を".$count."文字で要約してください";
	}

	/**
	 * @param int, string
	 */
	public static function saveAbstract(int $entryId, string $abstract){
		if(!function_exists("soycms_get_entry_attribute_object")) {
			include_once(SOY2::RootDir()."site_include/func/dao.php");
		}
		$attr = soycms_get_entry_attribute_object($entryId, self::FIELD_ID);
		$attr->setValue($abstract);
		$attr->setExtraValues(time());	// タイムスタンプも記録しておく
		soycms_save_entry_attribute_object($attr);
	}

	/**
	 * 指定の記事ID一覧分のカスタムフィールドの値を取得してグローバル変数に格納しておく
	 * @param array entryIds
	 */
	public static function setValuesByEntryIds(array $entryIds){
		if(!count($entryIds)) return;
		if(!isset($GLOBALS[self::GLOBAL_INDEX])) $GLOBALS[self::GLOBAL_INDEX] = array();

		try{
			$res = soycms_get_hash_table_dao("entry_attribute")->executeQuery(
				"SELECT entry_id, entry_field_id, entry_value, entry_extra_values ".
				"FROM EntryAttribute ".
				"WHERE entry_id IN (" . implode(",", $entryIds) . ") ".
				"AND entry_field_id = '".self::FIELD_ID."'"
			);
		}catch(Exception $e){
			$res = array();
		}

		if(count($res)){
			foreach($res as $v){
				if(!isset($v["entry_id"]) || !is_numeric($v["entry_id"])) continue;
				$entryId = (int)$v["entry_id"];
				if(!isset($GLOBALS[self::GLOBAL_INDEX][$entryId])) $GLOBALS[self::GLOBAL_INDEX][$entryId] = array();
				$extra = (isset($v["entry_extra_values"]) && is_string($v["entry_extra_values"]) && strlen($v["entry_extra_values"])) ? $v["entry_extra_values"] : null;
				$GLOBALS[self::GLOBAL_INDEX][$entryId] = array("value" => $v["entry_value"], "extraValues" => $extra);
			}
		}
		
		//値が取得できなかったもの
		foreach($entryIds as $entryId){
			if(!isset($GLOBALS[self::GLOBAL_INDEX][$entryId])) $GLOBALS[self::GLOBAL_INDEX][$entryId] = array();
		}
	}

	/**
	 * @param int
	 * @return array
	 */
	public static function getValuesByEntryId(int $entryId){
		return (isset($GLOBALS[self::GLOBAL_INDEX][$entryId])) ? $GLOBALS[self::GLOBAL_INDEX][$entryId] : array(); 
	}

	public static function getCount(){
		SOY2::import("domain.cms.DataSets");
		return DataSets::get(self::DATA_SET_KEY.".count", self::COUNT_OF_CHARS);
	}

	public static function saveCount(int $count){
		SOY2::import("domain.cms.DataSets");
		DataSets::put(self::DATA_SET_KEY.".count", $count);
	}

	public static function saveEnabledBlogPages(array $chks){
		SOY2::import("domain.cms.DataSets");
		$v = (count($chks)) ? soy2_serialize($chks) : "";
		DataSets::put(self::DATA_SET_KEY.".enabled", $v);
	}

	public static function getEnabledBlogPages(){
		SOY2::import("domain.cms.DataSets");
		$v = DataSets::get(self::DATA_SET_KEY.".enabled", "");
		return (strlen($v)) ? soy2_unserialize($v) : array();
	}

	public static function getPrefix(){
		SOY2::import("domain.cms.DataSets");
		return DataSets::get(self::DATA_SET_KEY.".prefix", "/** <a href=\"https://gemini.google.com/app\" target=\"_blank\" rel=\"noopener\">Gemini</a>が自動生成した概要 **/<br>");
	}

	public static function savePrefix(string $str){
		SOY2::import("domain.cms.DataSets");
		DataSets::put(self::DATA_SET_KEY.".prefix", $str);
	}

	public static function getPostfix(){
		SOY2::import("domain.cms.DataSets");
		return DataSets::get(self::DATA_SET_KEY.".postfix", "");
	}

	public static function savePostfix(string $str){
		SOY2::import("domain.cms.DataSets");
		DataSets::put(self::DATA_SET_KEY.".postfix", $str);
	}

	public static function isAbstractUpdate(){
		SOY2::import("domain.cms.DataSets");
		$on = DataSets::get(self::DATA_SET_KEY.".abstract_update", 1);
		return ((int)$on === 1);
	}

	public static function saveIsAbstractUpdate(bool $on){
		SOY2::import("domain.cms.DataSets");
		$v = ($on) ? 1 : 0;
		DataSets::put(self::DATA_SET_KEY.".abstract_update", $v);
	}
}