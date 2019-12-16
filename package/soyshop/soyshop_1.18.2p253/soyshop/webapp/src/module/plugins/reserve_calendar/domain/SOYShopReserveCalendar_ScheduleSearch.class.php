<?php

/**
 * @table soyshop_reserve_calendar_schedule_search
 */
class SOYShopReserveCalendar_ScheduleSearch {

	/**
	 * @column schedule_id
	 */
	private $scheduleId;

	/**
	 * @column schedule_date
	 */
	private $scheduleDate;

	function getScheduleId(){
		return $this->scheduleId;
	}
	function setScheduleId($scheduleId){
		$this->scheduleId = $scheduleId;
	}

	function getScheduleDate(){
		return $this->scheduleDate;
	}
	function setScheduleDate($scheduleDate){
		$this->scheduleDate = $scheduleDate;
	}
}
