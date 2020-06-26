<?php
/**
 * @table soymail_reservation
 */
class SOYMail_Reservation{
	
	const NO_SEND = 0;
	const IS_SEND = 1;
	
	/**
	 * @id
	 */
	private $id;
	
	/**
	 * @column mail_id
	 */
	private $mailId;
	
	/**
	 * @column is_send
	 */
	private $isSend;
	
	private $offset=0;
	
	/**
	 * @column reserve_date
	 */
	private $reserveDate;
	
	/**
	 * @column schedule_date
	 */
	private $scheduleDate;
	
	/**
	 * @column send_date
	 */
	private $sendDate;
	
	function getId(){
		return $this->id;
	}
	function setId($id){
		$this->id = $id;
	}
	
	function getMailId(){
		return $this->mailId;
	}
	function setMailId($mailId){
		$this->mailId = $mailId;
	}
	
	function getIsSend(){
		return $this->isSend;
	}
	function setIsSend($isSend){
		$this->isSend = $isSend;
	}
	
	function getOffset(){
		return $this->offset;
	}
	function setOffset($offset){
		$this->offset = $offset;
	}
	
	function getReserveDate(){
		return $this->reserveDate;
	}
	function setReserveDate($reserveDate){
		$this->reserveDate = $reserveDate;
	}
	
	function getScheduleDate(){
		return $this->scheduleDate;
	}
	function setScheduleDate($scheduleDate){
		$this->scheduleDate = $scheduleDate;
	}
	
	function getSendDate(){
		return $this->sendDate;
	}
	function setSendDate($sendDate){
		$this->sendDate = $sendDate;
	}
}

?>