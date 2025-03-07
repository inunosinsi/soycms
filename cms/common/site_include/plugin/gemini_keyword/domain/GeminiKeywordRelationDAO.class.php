<?php
SOY2::import("site_include.plugin.gemini_keyword.domain.GeminiKeywordRelation");
/**
 * @entity GeminiKeywordRelation
 */
abstract class GeminiKeywordRelationDAO extends SOY2DAO {

	abstract function insert(GeminiKeywordRelation $bean);

	abstract function deleteByEntryId(int $entryId);

	/**
	 * @final
	 * @param int
	 * @return array
	 */
	function getByEntryId(int $entryId){
		try{
			$res = $this->executeQuery(
				"SELECT d.keyword FROM GeminiKeywordRelation r ".
				"INNER JOIN GeminiKeyword k ".
				"ON r.keyword_id = k.id ".
				"INNER JOIN GeminiKeywordDictionary d ".
				"ON k.keyword_id = d.id ".
				"WHERE r.entry_id = :entryId", 
				array(":entryId" => $entryId)
			);
		}catch(Exception $e){
			$res = array();
		}
		if(!count($res)) return array();

		$_arr = array();
		foreach($res as $v){
			if(is_numeric(array_search($v["keyword"], $_arr))) continue;
			$_arr[] = $v["keyword"];
		}
		return $_arr;
	}
}
