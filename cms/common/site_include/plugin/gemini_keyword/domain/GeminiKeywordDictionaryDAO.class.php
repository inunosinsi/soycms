<?php
SOY2::import("site_include.plugin.gemini_keyword.domain.GeminiKeywordDictionary");
/**
 * @entity GeminiKeywordDictionary
 */
abstract class GeminiKeywordDictionaryDAO extends SOY2DAO {

	abstract function insert(GeminiKeywordDictionary $bean);

	/**
	 * @final
	 * @param array
	 * @return array
	 */
	function getKeywordList(array $keywords){
		if(!count($keywords)) return array();
	
		$sql = "SELECT * FROM GeminiKeywordDictionary ";
		$binds = array();
		$q = array();

		foreach($keywords as $idx => $k){
			$k = trim($k);
			$q[] = "keyword = :k".$idx;
			$binds[":k".$idx] = $k;
		}

		$sql .= "WHERE ".implode(" OR ", $q) . " ORDER BY id ASC";

		try{
			$res = $this->executeQuery($sql, $binds);
		}catch(Exception $e){
			$res = array();
		}
		if(!count($res)) return array();

		$_dic = array();
		foreach($res as $v){
			$_dic[(int)$v["id"]] = $v["keyword"];
		}
		
		return $_dic;
	}

	function getKeywords(){
		try{
			$res = $this->executeQuery("SELECT keyword FROM GeminiKeywordDictionary");
		}catch(Exception $e){
			return array();
		}

		if(!count($res)) return array();

		$_arr = array();
		foreach($res as $v){
			$_arr[] = $v["keyword"];
		}
		return $_arr;
	}

	/**
	 * @final
	 */
	function checkIsKeywordExists(string $v){
		try{
			$res = $this->executeQuery("SELECT id FROM GeminiKeywordDictionary WHERE keyword = :k", array(":k" => $v));
		}catch(Exception $e){
			return false;
		}
		return (isset($res[0]["id"]) && is_numeric($res[0]["id"]));
	}	
}
