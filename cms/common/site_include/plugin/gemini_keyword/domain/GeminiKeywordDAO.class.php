
<?php
SOY2::import("site_include.plugin.gemini_keyword.domain.GeminiKeyword");
/**
 * @entity GeminiKeyword
 */
abstract class GeminiKeywordDAO extends SOY2DAO {

	abstract function insert(GeminiKeyword $bean);

	/**
	 * @final
	 * @param array
	 * @return array
	 */
	function getIdsByKeywords(array $keywords){
		$sql = "SELECT k.id, d.keyword FROM GeminiKeyword k ".
				"INNER JOIN GeminiKeywordDictionary d ".
				"ON k.keyword_id = d.id ";
		$binds = array();
		$q = array();
			
		foreach($keywords as $idx => $k){
			$k = trim($k);
			$q[] = "d.keyword = :k".$idx;
			$binds[":k".$idx] = $k;
		}

		$sql .= "WHERE ".implode(" OR ", $q)." ORDER BY k.id";

		try{
			$res = $this->executeQuery($sql, $binds);
		}catch(Exception $e){
			$res = array();
		}
		if(!count($res)) return array();

		$_arr = array();
		foreach($res as $v){
			if(is_numeric(array_search($v["keyword"], $_arr))) continue;
			$_arr[(int)$v["id"]] = $v["keyword"];
		}
		return $_arr;
	}
}
