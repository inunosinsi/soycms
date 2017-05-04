<?php

/**
 * @table stepmail_send_history
 */
class StepMail_SendHistory{
	
	/**
	 * @column send_id
	 */
	private $sendId;
	
	/**
	 * @column send_date
	 */
	private $sendDate;
	
	function getSendId(){
		return $this->sendId;
	}
	function setSendId($sendId){
		$this->sendId = $sendId;
	}
	
	function getSendDate(){
		return $this->sendDate;
	}
	function setSendDate($sendDate){
		$this->sendDate = $sendDate;
	}
}
?>