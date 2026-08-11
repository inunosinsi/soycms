<?php

/**
 * @table soyshop_returns_slip_number
 */
class SOYShop_ReturnsSlipNumber {

	const IS_RETURN = 1;
	const NO_RETURN = 0;

	/**
	 * @id
	 */
	private $id;

	/**
	 * @column slip_number
	 */
	private $slipNumber;

	/**
	 * @column order_id
	 */
	private $orderId;

	/**
	 * @column is_return
	 */
	private $isReturn = 0;

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

	function getSlipNumber(){
		return $this->slipNumber;
	}
	function setSlipNumber($slipNumber){
		$this->slipNumber = $slipNumber;
	}

	function getOrderId(){
		return (is_numeric($this->orderId)) ? (int)$this->orderId : 0;
	}
	function setOrderId($orderId){
		$this->orderId = $orderId;
	}

	function getIsReturn(){
		return $this->isReturn;
	}
	function setIsReturn($isReturn){
		$this->isReturn = $isReturn;
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

	function getStatus(){
		switch($this->isReturn){
			case self::NO_RETURN:
				return "未返送";
			case self::IS_RETURN:
				return "返送済み";
		}
	}
}
