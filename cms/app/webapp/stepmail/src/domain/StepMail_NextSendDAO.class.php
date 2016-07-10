<?php

/**
 * @entity StepMail_NextSend
 */
abstract class StepMail_NextSendDAO extends SOY2DAO{
	
	/**
	 * @return id
	 * @trigger onInsert
	 */
	abstract function insert(StepMail_NextSend $bean);
	
	/**
	 * @trigger onUpdate
	 */
	abstract function update(StepMail_NextSend $bean);
	
	/**
	 * @return object
	 */
	abstract function getById($id);
	
	/**
	 * @return list
	 * @query is_sended != 1
	 * @order next_send_date ASC
	 */
	abstract function getNotSendUser();
	
	/**
	 * @return list
	 * @query is_sended != 1 AND next_send_date < :end
	 * @order id ASC
	 */
	abstract function getStepMailOfSendSchedule($end);
	
	/**
	 * @final
	 */
	function onInsert($query, $binds){
		$binds[":isSended"] = 0;
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