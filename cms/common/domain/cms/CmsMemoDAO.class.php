<?php
/**
 * @entity cms.CmsMemo
 */
abstract class CmsMemoDAO extends SOY2DAO {

	/**
	 * @trigger onInsert
	 */
	abstract function insert(CmsMemo $bean);

	/**
	 * @trigger onUpdate
	 */
	abstract function update(CmsMemo $bean);

	function getLatestMemo(){
		try{
			$res = $this->executeQuery("SELECT * FROM CmsMemo ORDER BY id DESC LIMIT 1");
		}catch(Exception $e){
			$res = array();
		}
		return (isset($res[0])) ? $this->getObject($res[0]) : new CmsMemo();
	}

	/**
	 * @final
	 */
	function onInsert($query, $binds){
		$binds[":createDate"] = time();
		$binds[":updateDate"] = time();
		return array($query, $binds);
	}

	/**
	 * @final
	 */
	function onUpdate($query, $binds){
		$binds[":updateDate"] = time();
		return array($query, $binds);
	}
}
