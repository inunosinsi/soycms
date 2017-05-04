<?php

/**
 * @entity StepMail_Mail
 */
abstract class StepMail_MailDAO extends SOY2DAO{
	
	/**
	 * @return id
	 * @trigger onInsert
	 */
	abstract function insert(StepMail_Mail $bean);
	
	/**
	 * @trigger onUpdate
	 */
	abstract function update(StepMail_Mail $bean);
	
	/**
	 * @return list
	 * @query is_disabled != 1
	 * @order id DESC
	 */
	abstract function get();
	
	/**
	 * @return object
	 */
	abstract function getById($id);
	
	/**
	 * @return object
	 */
	abstract function getByMailId($mailId);
	
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
?>