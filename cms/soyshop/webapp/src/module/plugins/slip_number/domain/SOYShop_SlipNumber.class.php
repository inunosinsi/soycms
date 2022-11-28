<?php

/**
 * @table soyshop_slip_number
 */
class SOYShop_SlipNumber {

	const IS_DELIVERY = 1;
	const NO_DELIVERY = 0;

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
	 * @column is_delivery
	 */
	private $isDelivery = 0;

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

	function getIsDelivery(){
		return $this->isDelivery;
	}
	function setIsDelivery($isDelivery){
		$this->isDelivery = $isDelivery;
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
		switch($this->isDelivery){
			case self::NO_DELIVERY:
				return "未発送";
			case self::IS_DELIVERY:
				return "発送済み";
		}
	}
}
