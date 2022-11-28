<?php
/**
 * @table soymail_errormail
 */
class ErrorMail {
	
	/**
	 * @id
	 */
	private $id;
	
	/**
	 * @column mail_id
	 */
	private $mailId;
	
	/**
	 * @column mail_content
	 */
	private $mailContent;
	
	/**
	 * @column mail_address
	 */
	private $mailAddress;
	
	/**
	 * @column receive_date
	 */
	private $receiveDate;

	function getId() {
		return $this->id;
	}
	function setId($id) {
		$this->id = $id;
	}
	function getMailId() {
		return $this->mailId;
	}
	function setMailId($mailId) {
		$this->mailId = $mailId;
	}
	function getMailContent() {
		return $this->mailContent;
	}
	function setMailContent($mailContent) {
		$this->mailContent = $mailContent;
	}
	function getMailAddress() {
		return $this->mailAddress;
	}
	function setMailAddress($mailAddress) {
		$this->mailAddress = $mailAddress;
	}
	function getReceiveDate() {
		return $this->receiveDate;
	}
	function setReceiveDate($receiveDate) {
		$this->receiveDate = $receiveDate;
	}
}
?>