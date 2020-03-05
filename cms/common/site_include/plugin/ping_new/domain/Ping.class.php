<?php

/**
 * @table Ping
 */
class Ping {

	/**
	 * @column entry_id
	 */
	private $entryId;

	/**
	 * @column send_date
	 */
	private $sendDate;

	function getEntryId(){
		return $this->entryId;
	}
	function setEntryId($entryId){
		$this->entryId = $entryId;
	}

	function getSendDate(){
		return $this->sendDate;
	}
	function setSendDate($sendDate){
		$this->sendDate = $sendDate;
	}
}
