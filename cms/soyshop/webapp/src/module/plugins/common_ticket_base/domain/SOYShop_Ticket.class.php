<?php
/**
 * @table soyshop_ticket
 */
class SOYShop_Ticket {

	/**
	 * @column user_id
	 */
	private $userId;
	private $count;

	/**
	 * @column update_date
	 */
	private $updateDate;

	function getUserId(){
		return (is_numeric($this->userId)) ? (int)$this->userId : 0;
	}
	function setUserId($userId){
		$this->userId = $userId;
	}

	function getCount(){
		return $this->count;
	}
	function setCount($count){
		$this->count = $count;
	}

	function getUpdateDate(){
		return $this->updateDate;
	}
	function setUpdateDate($updateDate){
		$this->updateDate = $updateDate;
	}
}
