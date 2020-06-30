<?php
/**
 * @entity admin.Memo
 */
abstract class MemoDAO extends SOY2DAO {

	/**
	 * @trigger onInsert
	 */
	abstract function insert(Memo $bean);

	/**
	 * @trigger onUpdate
	 */
	abstract function update(Memo $bean);

	function getLatestMemo(){
		try{
			$res = $this->executeQuery("SELECT * FROM Memo ORDER BY id DESC LIMIT 1");
		}catch(Exception $e){
			$res = array();
		}
		return (isset($res[0])) ? $this->getObject($res[0]) : new Memo();
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
