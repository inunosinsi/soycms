<?php

/**
 * @table stepmail_next_send
 */
class StepMail_NextSend{
	
	const NO_SENDED = 0;
	const IS_SENDED = 1;
	
	/**
	 * @id
	 */
	private $id;
	
	/**
	 * @column user_id
	 */
	private $userId;
	
	/**
	 * @column mail_id
	 */
	private $mailId;
	
	/**
	 * @column step_id
	 */
	private $stepId;
	
	/**
	 * @column next_send_date
	 */
	private $nextSendDate;
	
	/**
	 * @column is_sended
	 */
	private $isSended;
	
	function getId(){
		return $this->id;
	}
	function setId($id){
		$this->id = $id;
	}
	
	function getUserId(){
		return $this->userId;
	}
	function setUserId($userId){
		$this->userId = $userId;
	}
	
	function getMailId(){
		return $this->mailId;
	}
	function setMailId($mailId){
		$this->mailId = $mailId;
	}
	
	function getStepId(){
		return $this->stepId;
	}
	function setStepId($stepId){
		$this->stepId = $stepId;
	}
	
	function getNextSendDate(){
		return $this->nextSendDate;
	}
	function setNextSendDate($nextSendDate){
		$this->nextSendDate = $nextSendDate;
	}
	
	function getIsSended(){
		return $this->isSended;
	}
	function setIsSended($isSended){
		$this->isSended = $isSended;
	}
}
?>