<?php

class CustomSearchLogic extends SOY2LogicBase {

	function __construct(){}

	//検索用のデータベースを作成する
	function prepare(){
		$searchDao = soyshop_get_hash_table_dao("schedule_search");
		$lastScheduleId = $searchDao->getLastScheduleId();
		$values = soyshop_get_hash_table_dao("schedule_calendar")->getScheduleDates($lastScheduleId);
		if(count($values)){
			foreach($values as $schId => $timestamp){
				$obj = new SOYShopReserveCalendar_ScheduleSearch();
				$obj->setScheduleId($schId);
				$obj->setScheduleDate($timestamp);
				try{
					$searchDao->insert($obj);
				}catch(Exception $e){
					//var_dump($e);
				}
			}
		}
	}
}
