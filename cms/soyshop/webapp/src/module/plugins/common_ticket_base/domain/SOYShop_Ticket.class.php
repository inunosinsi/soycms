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
		return $this->userId;
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
