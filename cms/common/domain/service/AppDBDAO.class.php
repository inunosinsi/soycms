<?php

/**
 * @entity service.AppDB
 */
abstract class AppDBDAO extends SOY2DAO{
	
	/**
	 * @return id
	 * @trigger onInsert
	 */
	abstract function insert(AppDB $bean);
	
	/**
	 * @trigger onUpdate
	 */
	abstract function update(AppDB $bean);
	
	abstract function get();
	
	/**
	 * @return object
	 */
	abstract function getByAccountId($accountId);
	
	/**
	 * @return object
	 */
	abstract function getBySign($sign);
	
	abstract function deleteById($id);
	
	/**
	 * @final
	 */
	function onInsert($query, $binds){
		$binds[":registerDate"] = time();
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
?>