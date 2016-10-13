<?php

/**
 * @entity SOYShop_UserStorage
 */
abstract class SOYShop_UserStorageDAO extends SOY2DAO{
	
	/**
	 * @return id
	 * @trigger onInsert
	 */
	abstract function insert(SOYShop_UserStorage $bean);
	
	/**
	 * @return list
	 */
	abstract function getByUserId($userId);
	
	/**
	 * @return object
	 */
	abstract function getByToken($token);
	
	/**
	 * @final
	 */
	function onInsert($query, $binds){
		$binds[":uploadDate"] = time();
		return array($query, $binds);
	}
}
?>