<?php
SOY2::import("module.plugins.reserve_calendar.domain.SOYShopReserveCalendar_ScheduleSearch");
/**
 * @entity SOYShopReserveCalendar_ScheduleSearch
 */
abstract class SOYShopReserveCalendar_ScheduleSearchDAO extends SOY2DAO{

    /**
     * @return id
     */
    abstract function insert(SOYShopReserveCalendar_ScheduleSearch $bean);

    abstract function deleteById($id);

	/**
	 * @final
	 */
	function getLastScheduleId(){
		try{
			$res = $this->executeQuery("SELECT schedule_id FROM soyshop_reserve_calendar_schedule_search ORDER BY schedule_id DESC LIMIT 1");
		}catch(Exception $e){
			$res = array();
		}

		return (isset($res[0]["schedule_id"])) ? (int)$res[0]["schedule_id"] : 0;
	}
}
