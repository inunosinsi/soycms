<?php
class SmartScheduleLogic extends SOY2LogicBase{

    function __construct(){
        SOY2::imports("module.plugins.reserve_calendar.domain.*");
    }

    function getScheduleById($scheduleId){
        try{
            return soyshop_get_hash_table_dao("schedule_calendar")->getById($scheduleId);
        }catch(Exception $e){
            return new SOYShopReserveCalendar_Schedule();
        }
    }

    function getScheduleList($itemId, $year, $month){
		$schedules = array();	//タイムスタンプの配列に作り変える
        $list = soyshop_get_hash_table_dao("schedule_calendar")->getScheduleList($itemId, $year, $month);
		if(count($list)){
			foreach($list as $d => $v){
				$schedules[mktime(0, 0, 0, $month, $d, $year)] = $v;
			}
		}

		//次の月の分
		$month += 1;
		if($month > 12){
			$month -= 12;
			$year += 1;
		}

		$list = soyshop_get_hash_table_dao("schedule_calendar")->getScheduleList($itemId, $year, $month);
		if(count($list)){
			foreach($list as $d => $v){
				$schedules[mktime(0, 0, 0, $month, $d, $year)] = $v;
			}
		}

        return $schedules;
    }

	function findLatestScheduleDate($year, $month){
		return soyshop_get_hash_table_dao("schedule_calendar")->findLatestScheduleDate($year, $month);
	}
}
