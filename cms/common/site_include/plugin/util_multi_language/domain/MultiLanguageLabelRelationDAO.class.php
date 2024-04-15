<?php
SOY2::import("site_include.plugin.util_multi_language.domain.MultiLanguageLabelRelation");
/**
 * @entity MultiLanguageLabelRelation
 */
abstract class MultiLanguageLabelRelationDAO extends SOY2DAO {

	abstract function insert(MultiLanguageLabelRelation $bean);

	/**
	 * @query parent_label_id = :parentId
	 */
	abstract function update(MultiLanguageLabelRelation $bean);

	/**
	 * @return object
	 */
	abstract function getByChildId(int $childId);

	/**
	 * @query parent_label_id = :parentId AND lang = :lang
	 */
	abstract function delete(int $parentId, int $lang);

	/**
	 * @query parent_label_id = :parentId
	 */
	abstract function deleteByParentId(int $parentId);

	/**
	 * @final
	 * @param int
	 * @return array
	 */
	function getRelationListByParentId(int $parentId){
		try{
			$res = $this->executeQuery(
				"SELECT child_label_id, lang FROM MultiLanguageLabelRelation WHERE parent_label_id = :parentId",
				array(":parentId" => $parentId)
			);
		}catch(Exception $e){
			$res = array();
		}
		if(!count($res)) return array();

		$_arr = array();
		foreach($res as $v){
			$_arr[(int)$v["lang"]] = (int)$v["child_label_id"];
		}
		return $_arr;
	}

	/**
	 * @final
	 * @param int, string
	 * @return int
	 */
	function getRelationLabelIdByParentIdAndLang(int $parentId, string $lang){
		static $_arr;
		if(is_array($_arr) && isset($_arr[$parentId]) && is_numeric($_arr[$parentId])) return $_arr[$parentId];

		$_arr = array();

		SOY2::import("site_include.plugin.util_multi_language.util.SOYCMSUtilMultiLanguageUtil");

		try{
			$res = $this->executeQuery(
				"SELECT child_label_id FROM MultiLanguageLabelRelation WHERE parent_label_id = :parentId AND lang = :lang",
				array(":parentId" => $parentId, ":lang" => SOYCMSUtilMultiLanguageUtil::getLanguageIndex($lang))
			);
		}catch(Exception $e){
			$res = array();
		}
		if(!count($res)) return nulls;
		
		$_arr[$parentId] = (isset($res[0]["child_label_id"]) && (int)$res[0]["child_label_id"] > 0) ? (int)$res[0]["child_label_id"] : null;
		return $_arr[$parentId];
	}

	/**
	 * @final
	 * @param int
	 * @return int
	 */
	function getRelationLabelIdByChildId(int $childId){
		static $_arr;
		if(is_array($_arr) && isset($_arr[$childId]) && is_numeric($_arr[$childId])) return $_arr[$childId];

		$_arr = array();

		SOY2::import("site_include.plugin.util_multi_language.util.SOYCMSUtilMultiLanguageUtil");

		try{
			$res = $this->executeQuery(
				"SELECT parent_label_id FROM MultiLanguageLabelRelation WHERE child_label_id = :childId",
				array(":childId" => $childId)
			);
		}catch(Exception $e){
			$res = array();
		}
		if(!count($res)) return null;
		
		$_arr[$childId] = (isset($res[0]["parent_label_id"]) && (int)$res[0]["parent_label_id"] > 0) ? (int)$res[0]["parent_label_id"] : null;
		return $_arr[$childId];
	}

	/**
	 * @final
	 * @param array, string
	 * @return array
	 */
	function getRelationListByParentIdsAndLang(array $parentIds, string $lang){
		if(!count($parentIds)) return array();

		SOY2::import("site_include.plugin.util_multi_language.util.SOYCMSUtilMultiLanguageUtil");

		try{
			$res = $this->executeQuery(
				"SELECT parent_label_id, child_label_id FROM MultiLanguageLabelRelation WHERE parent_label_id IN (".implode(",", $parentIds).") AND lang = :lang",
				array(":lang" => SOYCMSUtilMultiLanguageUtil::getLanguageIndex($lang))
			);
		}catch(Exception $e){
			$res = array();
		}
		if(!count($res)) return array();
		
		$_arr = array();
		foreach($res as $v){
			$_arr[(int)$v["parent_label_id"]] = (int)$v["child_label_id"];
		}
		
		return $_arr;
	}

	/**
	 * @final
	 * @param array
	 * @return array
	 */
	function sortOutParentLabelIds(array $labelIds){
		if(!count($labelIds)) return array();

		try{
			$res = $this->executeQuery(
				"SELECT DISTINCT parent_label_id FROM MultiLanguageLabelRelation WHERE parent_label_id IN (".implode(",", $labelIds).")"
			);
		}catch(Exception $e){
			$res = array();
		}
		if(!count($res)) return array();

		$_arr = array();
		foreach($res as $v){
			$_arr[] = (int)$v["parent_label_id"];
		}
		return $_arr;
	}
}