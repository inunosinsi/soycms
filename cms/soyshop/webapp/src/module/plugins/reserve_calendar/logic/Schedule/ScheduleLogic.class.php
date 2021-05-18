<?php

class ScheduleLogic extends SOY2LogicBase{

    function __construct(){
        SOY2::imports("module.plugins.reserve_calendar.domain.*");
    }

    function getScheduleById($scheduleId){
        try{
            return self::dao()->getById($scheduleId);
        }catch(Exception $e){
            return new SOYShopReserveCalendar_Schedule();
        }
    }

    function getScheduleList($itemId, $year, $month){
        $list = self::dao()->getScheduleList($itemId, $year, $month);

		$nextY = $year;
		$nextM = $month + 1;
		if($nextM > 12) {
			$nextM = 1;
			$nextY += 1;
		}

        //スケジュールの自動登録 指定の年月でスケジュールの登録がない場合は自動登録
        if(!count($list) && (mktime(0, 0, 0, $nextM, 1, $nextY) - 1) > time()){
			self::_autoInsert($itemId, $year, $month);
            $list = self::dao()->getScheduleList($itemId, $year, $month);
        }

        return $list;
	}

    private function _autoInsert($itemId, $year, $month){
		SOY2::import("module.plugins.reserve_calendar.util.ReserveCalendarUtil");
		$cnf = ReserveCalendarUtil::getAutoConfig($itemId);
		if(!isset($cnf["register"]) || (int)$cnf["register"] != 1 && (int)$cnf["seat"] === 0) return true;

		$seat = (int)$cnf["seat"];

        //今月の日数を取得
        $logic = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Calendar.HolidayLogic", array("itemId" => $itemId));
        $d = $logic->getDayCount($year, $month);

        $dao = self::dao();

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

	function findLatestScheduleDate($year, $month){
		return self::dao()->findLatestScheduleDate($year, $month);
	}

	//指定の日から○日分の予定を取得する
	function getScheduleListFromDays($itemId, $now=null, $days=30){
		if(is_null($now)) $now = time();
		$now = soyshop_shape_timestamp($now);	//整形
		$list = self::dao()->getScheduleListFromDays($itemId, $now, $days);

		//自動登録
		if(!count($list)){
			$month = date("n", $now);
			self::_autoInsert($itemId, date("Y", $now), $month);

			$future = strtotime("+" . $days . "day", $now);
			if(date("n", $future) != $month){
				self::_autoInsert($itemId, date("Y", $future), date("n", $future));
			}
			$list = self::dao()->getScheduleListFromDays($itemId, $now, $days);
		}
		return $list;
	}

    private function dao(){
        static $dao;
        if(is_null($dao)) $dao = SOY2DAOFactory::create("SOYShopReserveCalendar_ScheduleDAO");
        return $dao;
    }
}
