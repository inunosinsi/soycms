<?php

/**
 * @entity StepMail_SendHistory
 */
abstract class StepMail_SendHistoryDAO extends SOY2DAO{
	
	/**
	 * @return id
	 * @trigger onInsert
	 */
	abstract function insert(StepMail_SendHistory $bean);
	
	/**
	 * @trigger onUpdate
	 */
	abstract function update(StepMail_SendHistory $bean);
	
	/**
	 * @final
	 */
	function onInsert($query, $binds){
		$binds[":sendDate"] = time();
		return array($query, $binds);
	}
	
	/**
	 * @final
	 */
	function onUpdate($query, $binds){
		
		return array($query, $binds);
	}
}
?>