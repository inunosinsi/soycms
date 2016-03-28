<?php

/**
 * @entity RecordDeadLink
 */
abstract class RecordDeadLinkDAO extends SOY2DAO{
	
	/**
	 * @return id
	 * @trigger onInsert
	 */
	abstract function insert(RecordDeadLink $bean);
	
	/**
	 * @return list
	 * @order register_date DESC
	 */
	abstract function get();
	
	/**
	 * @final
	 */
	function onInsert($query, $binds){
		$binds[":registerDate"] = time();
		
		return array($query, $binds);
	}
}
?>