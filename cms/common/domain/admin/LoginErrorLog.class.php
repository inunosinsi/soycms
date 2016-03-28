<?php

/**
 * @table LoginErrorLog
 */
class LoginErrorLog {
		
	/**
	 * @id
	 */
	private $id;
	
	private $ip;
	private $count;	//ログインを何回挑戦したか？
	
	private $successed;
	
	/**
	 * @column start_date
	 */
	private $startDate;
	
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
	
	function getIp(){
		return $this->ip;
	}
	function setIp($ip){
		$this->ip = $ip;
	}
	
	function getCount(){
		return $this->count;
	}
	function setCount($count){
		$this->count = $count;
	}
	
	function getSuccessed(){
		return $this->successed;
	}
	
	function setSuccessed($successed){
		$this->successed = $successed;
	}
	
	function getStartDate(){
		return $this->startDate;
	}
	function setStartDate($startDate){
		$this->startDate = $startDate;
	}
	
	function getUpdateDate(){
		return $this->updateDate;
	}
	function setUpdateDate($updateDate){
		$this->updateDate = $updateDate;
	}
}
?>