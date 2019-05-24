<?php
SOY2::import("module.plugins.reserve_calendar.domain.SOYShopReserveCalendar_SchedulePrice");
/**
 * @entity SOYShopReserveCalendar_SchedulePrice
 */
abstract class SOYShopReserveCalendar_SchedulePriceDAO extends SOY2DAO {

	/**
     * @return id
     */
    abstract function insert(SOYShopReserveCalendar_SchedulePrice $bean);

    abstract function update(SOYShopReserveCalendar_SchedulePrice $bean);

	abstract function getByScheduleId($scheduleId);

	/**
	 * @return object
	 * @query schedule_id = :scheduleId AND field_id = :fieldId
	 */
	abstract function get($scheduleId, $fieldId);

	/**
	 * @final
	 */
	function getPriceListByYearAndMonth($year, $month){
		$sql = "SELECT p.* FROM soyshop_reserve_calendar_schedule_price p ".
				"INNER JOIN soyshop_reserve_calendar_schedule sch ".
				"ON p.schedule_id = sch.id ".
				"WHERE sch.year = :y ".
				"AND sch.month = :m";

		try{
			$res = $this->executeQuery($sql, array(":y" => $year, ":m" => $month));
		}catch(Exception $e){
			$res = array();
		}

		if(!count($res)) return array();

		$list = array();
		foreach($res as $v){
			$list[$v["schedule_id"]][$v["field_id"]] = array("label" => $v["label"], "price" => $v["price"]);
		}
		return $list;
	}
}
