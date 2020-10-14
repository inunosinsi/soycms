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
	 * @return object
	 */
	abstract function getByScheduleId($scheduleId);

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

	function getSchedulePeriodByItemId($itemId, $start = null, $end = null){
		if(is_null($start) || !is_numeric($start)) $start = strtotime("-1 day");
		$sql = "SELECT MIN(schedule_date) AS MIN, MAX(schedule_date) AS MAX ".
				"FROM soyshop_reserve_calendar_schedule_search search ".
				"INNER JOIN soyshop_reserve_calendar_schedule sch ".
				"ON search.schedule_id = sch.id ".
				"WHERE sch.item_id = :itemId ".
				"AND search.schedule_date >= " . $start;

		//スケジュールの日時も加味
		if(isset($end) && is_numeric($end)){
			$sql .= " AND search.schedule_date <= " . $end;
		}

		try{
			$res = $this->executeQuery($sql, array(":itemId" => $itemId));
		}catch(Exception $e){
			$res = array();
		}

		if(!isset($res[0])) return array(0, 0);

		$min = (isset($res[0]["MIN"])) ? (int)$res[0]["MIN"] : 0;
		$max = (isset($res[0]["MAX"])) ? (int)$res[0]["MAX"] : 0;
		return array($min, $max);
	}
}
