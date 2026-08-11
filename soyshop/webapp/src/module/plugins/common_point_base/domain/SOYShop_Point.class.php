<?php
/**
 * @table soyshop_point
 */
class SOYShop_Point {

	/**
	 * @column user_id
	 */
	private $userId;
	private $point;

	/**
	 * @column create_date
	 */
	private $createDate;

	/**
	 * @column update_date
	 */
	private $updateDate;

	/**
	 * @column time_limit
	 */
	private $timeLimit;

	function getUserId(){
		return (is_numeric($this->userId)) ? (int)$this->userId : 0;
	}
	function setUserId($userId){
		$this->userId = $userId;
	}

	function getPoint(){
		return (is_numeric($this->point)) ? (int)$this->point : 0;
	}
	function setPoint($point){
		$this->point = $point;
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

	function getTimeLimit(){
		return $this->timeLimit;
	}
	function setTimeLimit($timeLimit){
		$this->timeLimit = $timeLimit;
	}
}
