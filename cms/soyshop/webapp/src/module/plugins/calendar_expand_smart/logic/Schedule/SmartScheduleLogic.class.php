<?php
class SmartScheduleLogic extends SOY2LogicBase{

    function __construct(){
        SOY2::imports("module.plugins.reserve_calendar.domain.*");
    }

    function getScheduleById(int $scheduleId){
        try{
            return soyshop_get_hash_table_dao("schedule_calendar")->getById($scheduleId);
        }catch(Exception $e){
            return new SOYShopReserveCalendar_Schedule();
        }
    }

    function getScheduleList(int $itemId, int $year, int $month, int $addMonth=1){
		$schedules = array();	//タイムスタンプの配列に作り変える
		for($i = 0; $i <= $addMonth; $i++){
			$y = $year;
			$m = $month + $i;
			if($m > 12){
				$y += 1;
				$m -= 12;
			}
			$list = soyshop_get_hash_table_dao("schedule_calendar")->getScheduleList($itemId, $y, $m);
			if(!count($list)) continue;

			foreach($list as $d => $v){
				$schedules[mktime(0, 0, 0, $m, $d, $y)] = $v;
			}
		}

        return $schedules;
    }

	function findLatestScheduleDate(int $year, int $month){
		return soyshop_get_hash_table_dao("schedule_calendar")->findLatestScheduleDate($year, $month);
	}
}
