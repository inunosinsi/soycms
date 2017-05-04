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
	
	function getNoSendStepMailList($lim = 15){
		$sql = "SELECT user.name, user.mail_address, next.*, step.title AS step_title, mail.title AS mail_title FROM stepmail_next_send next " .
				"INNER JOIN soyshop_user user ".
				"ON next.user_id = user.id ".
				"INNER JOIN stepmail_step step ".
				"ON next.step_id = step.id ".
				"INNER JOIN stepmail_mail mail ".
				"ON next.mail_id = mail.id ".
				"WHERE next.is_sended = " . StepMail_NextSend::NO_SENDED . " ".
				"AND user.is_disabled != 1 ".
				"ORDER BY next.next_send_date ASC ".
				"LIMIT " . $lim;
		
		try{
			return $this->executeQuery($sql, array());
		}catch(Exception $e){
			return array();
		}
	}
	
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