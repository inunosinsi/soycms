<?php
/**
 * @table soyshop_deposit_manager_deposit
 */
class SOYShop_DepositManagerDeposit {

	/**
	 * @id
	 */
	private $id;

	/**
	 * @column user_id
	 */
	private $userId;

	/**
	 * @column subject_id
	 */
	private $subjectId;

	/**
	 * @column deposit_price
	 */
	private $depositPrice;

	/**
	 * @column deposit_date
	 */
	private $depositDate;

	private $memo;

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

	function getUserId(){
		return $this->userId;
	}
	function setUserId($userId){
		$this->userId = $userId;
	}

	function getSubjectId(){
		return $this->subjectId;
	}
	function setSubjectId($subjectId){
		$this->subjectId = $subjectId;
	}

	function getDepositPrice(){
		return $this->depositPrice;
	}
	function setDepositPrice($depositPrice){
		$this->depositPrice = $depositPrice;
	}

	function getDepositDate(){
		return $this->depositDate;
	}
	function setDepositDate($depositDate){
		$this->depositDate = $depositDate;
	}

	function getMemo(){
		return $this->memo;
	}
	function setMemo($memo){
		$this->memo = $memo;
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
