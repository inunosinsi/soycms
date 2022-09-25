<?php

class ScheduleLogic extends SOY2LogicBase{

    function __construct(){
        SOY2::imports("module.plugins.reserve_calendar.domain.*");
    }

    /**
     * @param int
     * @return SOYShopReserveCalendar_Schedule
     */
    function getScheduleById(int $scheduleId){
        try{
            return soyshop_get_hash_table_dao("schedule_calendar")->getById($scheduleId);
        }catch(Exception $e){
            return new SOYShopReserveCalendar_Schedule();
        }
    }

    /**
     * @param int, int, int
     * @return array
     */
    function getScheduleList(int $itemId, int $year, int $month){
        $list = soyshop_get_hash_table_dao("schedule_calendar")->getScheduleList($itemId, $year, $month);

		$nextY = $year;
		$nextM = $month + 1;
		if($nextM > 12) {
			$nextM = 1;
			$nextY += 1;
		}

        //スケジュールの自動登録 指定の年月でスケジュールの登録がない場合は自動登録
        if(!count($list) && (mktime(0, 0, 0, $nextM, 1, $nextY) - 1) > time()){
			self::_autoInsert($itemId, $year, $month);
            $list = soyshop_get_hash_table_dao("schedule_calendar")->getScheduleList($itemId, $year, $month);
        }

        return $list;
	}

    /**
     * @param int, int, int
     * @return bool
     */
    private function _autoInsert(int $itemId, int $year, int $month){
		SOY2::import("module.plugins.reserve_calendar.util.ReserveCalendarUtil");
		$cnf = ReserveCalendarUtil::getAutoConfig($itemId);
		if(!isset($cnf["register"]) || (int)$cnf["register"] != 1 && (int)$cnf["seat"] === 0) return true;

		$seat = (int)$cnf["seat"];

        //今月の日数を取得
        $logic = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Calendar.HolidayLogic", array("itemId" => $itemId));
        $d = $logic->getDayCount($year, $month);

        $dao = soyshop_get_hash_table_dao("schedule_calendar");

        $labelList = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Calendar.LabelLogic")->getLabelList($itemId);

        for ($i = 1; $i <= $d; $i++) {
            if($logic->isBD(mktime(0, 0, 0, $month, $i, $year))){ //営業日のみに絞る
				foreach($labelList as $labelId => $label){
                    $obj = new SOYShopReserveCalendar_Schedule();
                    $obj->setItemId($itemId);
                    $obj->setLabelId($labelId);
                    $obj->setYear($year);
                    $obj->setMonth($month);
                    $obj->setDay($i);
                    $obj->setUnsoldSeat($seat);

                    try{
                        $dao->insert($obj);
                    }catch(Exception $e){
						//
                    }
                }
            }
        }

        return true;
    }

	function findLatestScheduleDate(int $year, int $month){
		return soyshop_get_hash_table_dao("schedule_calendar")->findLatestScheduleDate($year, $month);
	}

	/**
     * 指定の日から○日分の予定を取得する
     * 5番目の引数には想定された件数を取得できなかった場合はスケジュールの自動登録を行う
     * @param int, int, int, int, int
     * @return array
     */
	function getScheduleListFromDays(int $itemId, int $now=0, int $days=30, int $deadline=0, int $assumption=0){
		if($now === 0) $now = time();
		$now = soyshop_shape_timestamp($now);	//整形
		$list = soyshop_get_hash_table_dao("schedule_calendar")->getScheduleListFromDays($itemId, $now, $days, $deadline);
        $cnt = count($list);

		//自動登録
		if($cnt === 0 || ($assumption > 0 && $cnt < $assumption)){
			$month = date("n", $now);
			self::_autoInsert($itemId, date("Y", $now), $month);

			$future = strtotime("+" . $days . "day", $now);
			if(date("n", $future) != $month){
				self::_autoInsert($itemId, date("Y", $future), date("n", $future));
			}
			$list = soyshop_get_hash_table_dao("schedule_calendar")->getScheduleListFromDays($itemId, $now, $days, $deadline);
		}
		return $list;
	}
}
