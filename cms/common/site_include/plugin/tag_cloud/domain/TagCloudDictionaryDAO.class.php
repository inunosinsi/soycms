<?php
SOY2::import("site_include.plugin.tag_cloud.domain.TagCloudDictionary");
/**
 * @entity TagCloudDictionary
 */
abstract class TagCloudDictionaryDAO extends SOY2DAO{

	/**
	 * @return id
	 * @trigger onUpdate
	 */
	abstract function insert(TagCloudDictionary $bean);

	/**
	 * @trigger onUpdate
	 */
	abstract function update(TagCloudDictionary $bean);

	abstract function get();

	/**
	 * @return object
	 */
	abstract function getById($id);

	/**
	 * @return object
	 */
	abstract function getByWord($word);

	/**
	 * @return object
	 */
	abstract function getByHash($hash);

	/**
	 * @final
	 */
	function getNoHashWordIds(){
		try{
			$res = $this->executeQuery("SELECT id FROM TagCloudDictionary WHERE hash IS NULL OR hash = ''");
		}catch(Exception $e){
			$res = array();
		}
		if(!count($res)) return array();

		$list = array();
		foreach($res as $v){
			$list[] = $v["id"];
		}
		return $list;
	}

	/**
	 * @final
	 */
	function checkIsTagExists(string $v){
		try{
			$res = $this->executeQuery("SELECT id FROM TagCloudDictionary WHERE word = :word OR hash = :word", array(":word" => $v));
		}catch(Exception $e){
			return false;
		}
		return (isset($res[0]["id"]) && is_numeric($res[0]["id"]));
	}

	/**
	 * @final
	 */
	function getWordIdByWordOrHash(string $v){
		try{
			$res = $this->executeQuery("SELECT id FROM TagCloudDictionary WHERE word = :word OR hash = :word", array(":word" => $v));
		}catch(Exception $e){
			return false;
		}
		return (isset($res[0]["id"]) && is_numeric($res[0]["id"])) ? (int)$res[0]["id"] : 0;
	}

	/**
	 * @final
	 */
	function onUpdate($query, $binds){
		if(is_null($binds[":hash"])){
			SOY2::import("site_include.plugin.tag_cloud.util.TagCloudUtil");
			$binds[":hash"] = TagCloudUtil::generateHash(trim($binds[":word"]));
		}
		return array($query, $binds);
	}
}
