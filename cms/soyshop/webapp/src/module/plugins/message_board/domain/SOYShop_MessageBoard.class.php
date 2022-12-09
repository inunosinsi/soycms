<?php

/**
 * @table soyshop_message_board
 */
class SOYShop_MessageBoard {
	
	/**
	 * @id
	 */
	private $id;
	
	/**
	 * @column account_id
	 */
	private $accountId;
	private $message;
	
	/**
	 * @column create_date
	 */
	private $createDate;
	
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
	
	function getMessage(){
		return $this->message;
	}
	function setMessage($message){
		$this->message = $message;
	}
	
	function getCreateDate(){
		return $this->createDate;
	}
	function setCreateDate($createDate){
		$this->createDate = $createDate;
	}
}
?>