<?php
SOY2::import("module.plugins.tag_cloud.domain.SOYShop_TagCloudDictionary");
/**
 * @entity SOYShop_TagCloudDictionary
 */
abstract class SOYShop_TagCloudDictionaryDAO extends SOY2DAO{

	/**
	 * @return id
	 * @trigger onUpdate
	 */
	abstract function insert(SOYShop_TagCloudDictionary $bean);

	/**
	 * @trigger onUpdate
	 */
	abstract function update(SOYShop_TagCloudDictionary $bean);

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

	abstract function getByCategoryId($categoryId);

	/**
	 * @final
	 */
	function getNoHashWordIds(){
		try{
			$res = $this->executeQuery("SELECT id FROM soyshop_tag_cloud_dictionary WHERE hash IS NULL OR hash = ''");
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
	function getOtherThanUnclassifiedTags(){

		//カテゴリIDで埋めておく
		try{
			$res = $this->executeQuery("SELECT id FROM soyshop_tag_cloud_category ORDER BY id ASC");
		}catch(Exception $e){
			$res = array();
		}
		if(!count($res)) return array();

		$list = array();
		foreach($res as $v){
			if(!isset($v["id"])) continue;
			$id = (int)$v["id"];
			if(!isset($list[$id]) || !is_array($list[$id])) $list[$id] = array();
		}

		try{
			$res = $this->executeQuery("SELECT * FROM soyshop_tag_cloud_dictionary WHERE category_id > 0");
		}catch(Exception $e){
			$res = array();
		}
		if(!count($res)) return array();

		foreach($res as $v){
			$list[$v["category_id"]][] = $this->getObject($v);
		}
		return $list;
	}

	/**
	 * @final
	 */
	function onUpdate($query, $binds){
		if(is_null($binds[":hash"])){
			SOY2::import("module.plugins.tag_cloud.util.TagCloudUtil");
			$binds[":hash"] = TagCloudUtil::generateHash(trim($binds[":word"]));
		}
		if(!isset($binds[":categoryId"])) $binds[":categoryId"] = 0;
		return array($query, $binds);
	}
}
