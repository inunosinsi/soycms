<?php

class SOYCalendarConnectLogic extends SOY2LogicBase {

	function __construct(){}

	function insertSchedule(){
		//定休日の方のJSONを取得
		foreach(array("holiday", "business") as $key){
			$json = soyshop_get_page_url("soycalendar_".$key.".json");
			if(!file_exists($json)) continue;

			$resp = @file_get_contents($json);
			if(is_null($resp)) continue;
			
			$_arr = json_decode($resp, true);
			if(is_array($_arr) && count($_arr)){
				SOY2::import("module.plugins.parts_calendar.common.PartsCalendarCommon");
				switch($key){
					case "holiday":
						$ymd = PartsCalendarCommon::getYmdConfig();
						break;
					case "business":
						$ymd = PartsCalendarCommon::getBDConfig();
						break;
				}
				
				foreach($_arr as $scheduleDate){
					$dateStr = date("Y/m/d", $scheduleDate);
					if(
						is_numeric(array_search($dateStr, $ymd)) || 
						is_numeric(array_search(date("Y/n/d", $scheduleDate), $ymd)) || 
						is_numeric(array_search(date("Y/m/j", $scheduleDate), $ymd)) || 
						is_numeric(array_search(date("Y/n/j", $scheduleDate), $ymd))
					) {
						continue;
					}

					$ymd[] = $dateStr; 
				}

				// 古いデータの自動削除
				if(count($ymd)){
					$soyCalCnf = PartsCalendarCommon::getSOYCalendarConnectConfig();
					if(isset($soyCalCnf["auto_delete"]) && (int)$soyCalCnf["auto_delete"] > 0){
						$upperLimit = strtotime("-".$soyCalCnf["auto_delete"]."month");
						$tmp = array();
						foreach($ymd as $d){
							if(soyshop_convert_timestamp($d) < $upperLimit) continue;
							$tmp[] = $d;
						}
						$ymd = $tmp;
					}

				}

				rsort($ymd);
				switch($key){
					case "holiday":
						PartsCalendarCommon::saveConfig("ymd", PartsCalendarCommon::ymd(implode("\n", $ymd)));
						break;
					case "business":
						PartsCalendarCommon::saveConfig("business_day", PartsCalendarCommon::ymd(implode("\n", $ymd)));
						break;
				}				
			}
		}
	}

	function getTitleList(){
		$old = SOYAppUtil::switchAppMode("calendar");

		try{
			$res = SOY2DAOFactory::create("SOYCalendar_TitleDAO")->executeQuery(
				"SELECT id, title FROM soycalendar_title "
			);
		}catch(Exception $e){
			$res = array();
		}
		
		$_arr = array();
		if(count($res)){
			foreach($res as $v){
				$_arr[(int)$v["id"]] = $v["title"];
			}
		}

		SOYAppUtil::resetAppMode($old);

		return $_arr;
	}
}
