<?php
/**
 * @entity memo.SOYShop_Memo
 */
abstract class SOYShop_MemoDAO extends SOY2DAO {

	/**
	 * @trigger onInsert
	 */
	abstract function insert(SOYShop_Memo $bean);

	/**
	 * @trigger onUpdate
	 */
	abstract function update(SOYShop_Memo $bean);

	function getLatestMemo(){
		try{
			$res = $this->executeQuery("SELECT * FROM soyshop_memo ORDER BY id DESC LIMIT 1");
		}catch(Exception $e){
			$res = array();
		}
		return (isset($res[0])) ? $this->getObject($res[0]) : new SOYShop_Memo();
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
