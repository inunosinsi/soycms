<?php

class ReserveLogic extends SOY2LogicBase{
	
	function __construct(){
		SOY2::import("module.plugins.reserve_calendar.domain.SOYShopReserveCalendar_Reserve");
		SOY2::import("module.plugins.reserve_calendar.domain.SOYShopReserveCalendar_ReserveDAO");
	}
	
	function getReservedSchedules($time = null, $limit = 16){
		if(is_null($time)) $time = time();
		
		return self::dao()->getReservedSchedules($time, $limit);
	}
	
	function getReservedListByScheduleId($scheduleId){
		return self::dao()->getReservedListByScheduleId($scheduleId);
	}
	
	function getReservedSchedulesByPeriod($year = null, $month = null){
		//どちらかが指定されていない時は動きません
		if(is_null($year) || is_null($month)) return array();
		
		//schedule_idと予約数を返す
		return self::dao()->getReservedSchedulesByPeriod($year, $month);
	}
	
	function checkIsUnsoldSeatByScheduleId($scheduleId){
		
		//boolean
		return self::dao()->checkIsUnsoldSeatByScheduleId($scheduleId);
	}
	
	private function dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("SOYShopReserveCalendar_ReserveDAO");
		return $dao;
	}
}
?>