<?php

/**
 * @table soyshop_inquiry
 */
class SOYShop_Inquiry {

	const NO_CONFIRM = 0;
	const IS_CONFIRM = 1;

	/**
	 * @id
	 */
	private $id;

	/**
	 * @column tracking_number
	 */
	private $trackingNumber;

	/**
	 * @column user_id
	 */
	private $userId;

	/**
	 * @column mail_log_id
	 */
	private $mailLogId;
	private $requirement;
	private $content;

	/**
	 * @column is_confirm
	 */
	private $isConfirm;

	/**
	 * @column create_date
	 */
	private $createDate;

	/**
	 * @column update_date
	 */
	private $updateDate;

	function getId(){
		return $this->id;
	}
	function setId($id){
		$this->id = $id;
	}

	function getTrackingNumber(){
		return $this->trackingNumber;
	}
	function setTrackingNumber($trackingNumber){
		$this->trackingNumber = $trackingNumber;
	}

	function getUserId(){
		return (is_numeric($this->userId)) ? (int)$this->userId : 0;
	}
	function setUserId($userId){
		$this->userId = $userId;
	}

	function getMailLogId(){
		return $this->mailLogId;
	}
	function setMailLogId($mailLogId){
		$this->mailLogId = $mailLogId;
	}

	function getRequirement(){
		return $this->requirement;
	}
	function setRequirement($requirement){
		$this->requirement = $requirement;
	}

	function getContent(){
		return $this->content;
	}
	function setContent($content){
		$this->content = $content;
	}

	function getIsConfirm(){
		return $this->isConfirm;
	}
	function setIsConfirm($isConfirm){
		$this->isConfirm = $isConfirm;
	}

	function getCreateDate(){
		return $this->createDate;
	}
	function setCreateDate($createDate){
		$this->createDate = $createDate;
	}

	function getUpdateDate(){
		return $this->updateDate;
	}
	function setUpdateDate($updateDate){
		$this->updateDate = $updateDate;
	}
}
