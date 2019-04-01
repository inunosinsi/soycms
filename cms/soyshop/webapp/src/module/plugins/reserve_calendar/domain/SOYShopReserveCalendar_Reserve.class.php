<?php

/**
 * @table soyshop_reserve_calendar_reserve
 */
class SOYShopReserveCalendar_Reserve {

	const IS_TEMP = 1;	//仮登録
	const NO_TEMP = 0;	//本登録

	/**
	 * @id
	 */
	private $id;

	/**
	 * @column schedule_id
	 */
	private $scheduleId;

	/**
	 * @column order_id
	 */
	private $orderId;
	private $token;
	private $temp;

	/**
	 * @column temp_date
	 * 仮登録
	 */
	private $tempDate;

	/**
	 * @column reserve_date
	 */
	private $reserveDate;

	function getId(){
		return $this->id;
	}
	function setId($id){
		$this->id = $id;
	}

	function getScheduleId(){
		return $this->scheduleId;
	}
	function setScheduleId($scheduleId){
		$this->scheduleId = $scheduleId;
	}

	function getOrderId(){
		return $this->orderId;
	}
	function setOrderId($orderId){
		$this->orderId = $orderId;
	}

	function getToken(){
		return $this->token;
	}
	function setToken($token){
		$this->token = $token;
	}

	function getTemp(){
		return $this->temp;
	}
	function setTemp($temp){
		$this->temp = $temp;
	}

	function getTempDate(){
		return $this->tempDate;
	}
	function setTempDate($tempDate){
		$this->tempDate = $tempDate;
	}

	function getReserveDate(){
		return $this->reserveDate;
	}
	function setReserveDate($reserveDate){
		$this->reserveDate = $reserveDate;
	}
}
