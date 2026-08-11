<?php

/**
 * @entity SOYShop_MessageBoard
 */
abstract class SOYShop_MessageBoardDAO extends SOY2DAO{
	
	/**
	 * @return id
	 * @trigger onInsert
	 */
	abstract function insert(SOYShop_MessageBoard $bean);
	
	abstract function get();
	
	/**
	 * @final
	 */
	function onInsert($query, $binds){
		$binds[":createDate"] = time();
		
		return array($query, $binds);
	}
}
?>