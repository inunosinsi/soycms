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

        //スケジュールの自動登録 今日より後の日付でないと動作しない
        if(!count($list) && mktime(0, 0, 0, $month, 1, $year) > time()){
            SOY2::import("module.plugins.reserve_calendar.util.ReserveCalendarUtil");
            $config = ReserveCalendarUtil::getAutoConfig($itemId);
            if(isset($config["register"]) && (int)$config["register"] === 1 && (int)$config["seat"] > 0){
                self::autoInsert($itemId, $year, $month, (int)$config["seat"]);
                $list = self::dao()->getScheduleList($itemId, $year, $month);
            }
        }

        return $list;
    }

    private function autoInsert($itemId, $year, $month, $seat){
        //今月の日数を取得
        $logic = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Calendar.HolidayLogic", array("itemId" => $itemId));
        $d = $logic->getDayCount($year, $month);

        $dao = self::dao();

        $labelList = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Calendar.LabelLogic")->getLabelList($itemId);

        for ($i = 1; $i <= $d; $i++) {

            //営業日のみに絞る
            if($logic->isBD(mktime(0, 0, 0, $month, $i, $year))){

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

    private function dao(){
        static $dao;
        if(is_null($dao)) $dao = SOY2DAOFactory::create("SOYShopReserveCalendar_ScheduleDAO");
        return $dao;
    }
}
