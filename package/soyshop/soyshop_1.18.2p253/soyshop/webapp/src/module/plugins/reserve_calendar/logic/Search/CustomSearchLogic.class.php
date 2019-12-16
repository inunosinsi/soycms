<?php

class CustomSearchLogic extends SOY2LogicBase {

	function __construct(){

	}

	//検索用のデータベースを作成する
	function prepare(){
		$lastScheduleId = self::searchDao()->getLastScheduleId();
		$values = self::schDao()->getScheduleDates($lastScheduleId);
		if(count($values)){
			foreach($values as $schId => $timestamp){
				$obj = new SOYShopReserveCalendar_ScheduleSearch();
				$obj->setScheduleId($schId);
				$obj->setScheduleDate($timestamp);
				try{
					self::searchDao()->insert($obj);
				}catch(Exception $e){
					var_dump($e);
				}
			}
		}
	}

	function schDao(){
		static $dao;
		if(is_null($dao)){
			SOY2::import("module.plugins.reserve_calendar.domain.SOYShopReserveCalendar_ScheduleDAO");
			$dao = SOY2DAOFactory::create("SOYShopReserveCalendar_ScheduleDAO");
		}
		return $dao;
	}

	function searchDao(){
		static $dao;
		if(is_null($dao)){
			SOY2::import("module.plugins.reserve_calendar.domain.SOYShopReserveCalendar_ScheduleSearchDAO");
			$dao = SOY2DAOFactory::create("SOYShopReserveCalendar_ScheduleSearchDAO");
		}
		return $dao;
	}
}
