<?php

/**
 * @table soyshop_reserve_calendar_schedule_price
 */
class SOYShopReserveCalendar_SchedulePrice {

	/**
	 * @column schedule_id
	 */
	private $scheduleId;

	private $label;

	/**
	 * @column field_id
	 */
	private $fieldId;
	private $price;

	function getScheduleId(){
		return (is_numeric($this->scheduleId)) ? (int)$this->scheduleId : 0;
	}
	function setScheduleId($scheduleId){
		$this->scheduleId = $scheduleId;
	}

	function getLabel(){
		return $this->label;
	}
	function setLabel($label){
		$this->label = $label;
	}

	function getFieldId(){
		return $this->fieldId;
	}
	function setFieldId($fieldId){
		$this->fieldId = $fieldId;
	}

	function getPrice(){
		return $this->price;
	}
	function setPrice($price){
		$this->price = $price;
	}
}
