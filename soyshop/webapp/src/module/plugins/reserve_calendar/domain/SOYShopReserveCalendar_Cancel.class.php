<?php

/**
 * @table soyshop_reserve_calendar_cancel
 */
class SOYShopReserveCalendar_Cancel {

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

	/**
	 * @column cancel_date
	 */
	private $cancelDate;

	function getId(){
		return $this->id;
	}
	function setId($id){
		$this->id = $id;
	}

	function getScheduleId(){
		return (is_numeric($this->scheduleId)) ? (int)$this->scheduleId : 0;
	}
	function setScheduleId($scheduleId){
		$this->scheduleId = $scheduleId;
	}

	function getOrderId(){
		return (is_numeric($this->orderId)) ? (int)$this->orderId : 0;
	}
	function setOrderId($orderId){
		$this->orderId = $orderId;
	}

	function getCancelDate(){
		return $this->cancelDate;
	}
	function setCancelDate($cancelDate){
		$this->cancelDate = $cancelDate;
	}
}
