<?php

/**
 * @table AppDB
 */
class AppDB {
	
	/**
	 * @id
	 */
	private $id;
	
	/**
	 * @column account_id
	 */
	private $accountId;
	private $sign;
	
	/**
	 * @column register_date
	 */
	private $registerDate;
	
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
	
	function getAccountId(){
		return $this->accountId;
	}
	function setAccountId($accountId){
		$this->accountId = $accountId;
	}
	
	function getSign(){
		return $this->sign;
	}
	function setSign($sign){
		$this->sign = $sign;
	}
	
	function getRegisterDate(){
		return $this->registerDate;
	}
	function setRegisterDate($registerDate){
		$this->registerDate = $registerDate;
	}
	
	function getUpdateDate(){
		return $this->updateDate;
	}
	function setUpdateDate($updateDate){
		$this->updateDate = $updateDate;
	}
}
?>