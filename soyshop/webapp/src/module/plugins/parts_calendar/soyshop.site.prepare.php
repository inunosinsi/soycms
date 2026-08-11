<?php

class CalendarPrepare extends SOYShopSitePrepareAction{

	const DEBUG = 0;	//1を指定すると配列で出力する

	function prepare(){
		// soycalendar_connect.json
		if(!isset($_SERVER["REQUEST_URI"])) return;

		preg_match('/\/soycalendar_(.*?).json$/', $_SERVER["REQUEST_URI"], $tmp);
		if(!isset($tmp[1])) return;

		// JSONのファイル名で定休日 or 営業日の切り替えを行えるようにする
		SOY2::import("module.plugins.parts_calendar.common.PartsCalendarCommon");
		$soyCalCnf = PartsCalendarCommon::getSOYCalendarConnectConfig();

		switch($tmp[1]){
			case "business":
				$titleId = (isset($soyCalCnf[$tmp[1]]) && is_numeric($soyCalCnf[$tmp[1]])) ? (int)$soyCalCnf[$tmp[1]] : 0;
				break;
			case "holiday":
			default:
				$titleId = (isset($soyCalCnf["holiday"]) && is_numeric($soyCalCnf["holiday"])) ? (int)$soyCalCnf["holiday"] : 0;
				break;
		}

		if($titleId === 0) self::_output();
		
		SOY2::import("util.SOYAppUtil");
		$old = SOYAppUtil::switchAppMode("calendar");

		SOY2::import("util.CalendarAppUtil");
		SOY2::import("func.fn", ".php");
		
		// 本日以降のデータを取得する
		$dao = SOY2DAOFactory::create("SOYCalendar_ItemDAO");

		try{
			$res = $dao->executeQuery(
				"SELECT schedule_date ".
				"FROM soycalendar_item ".
				"WHERE schedule_date > :now ".
				"AND title_id = :titleId",
				array(":now" => strtotime("-1day"), ":titleId" => $titleId)
			);
		}catch(Exception $e){
			$res = array();
		}
		
		$_arr = array();
		if(count($res)) {
			foreach($res as $v){
				$_arr[] = $v["schedule_date"];
			}
		}

		SOYAppUtil::resetAppMode($old);

		self::_output($_arr);
	}

	private function _output(array $arr=array()){
		if(self::DEBUG){
			var_dump($arr);
		}else{
			header("Content-Type: application/json; charset=utf-8");
			echo json_encode($arr);
		}
		exit;
	}
}
SOYShopPlugin::extension("soyshop.site.prepare", "parts_calendar", "CalendarPrepare");
