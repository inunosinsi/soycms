<?php

class BusinessDateLogic extends SOY2LogicBase{

	function __construct(){
		SOY2::import("util.SOYShopPluginUtil");
		SOY2::import("module.plugins.parts_calendar.common.PartsCalendarCommon");
	}

	/**
	 * 次の営業日を調べる
	 * @param int
	 * @return int timestamp
	 */
	function getNextBusinessDate(int $i=0){

		$timestamp = time();

		//今日が定休日であるか調べる
		if($i === 0){
			if(!self::checkRegularHoliday($timestamp)) return $timestamp;
			$i++;
		}

		for(;;){
			$timestamp = strtotime("+" . $i++ . " day");
			if(!self::checkRegularHoliday($timestamp)) break;

			//無限ループに入るのを防止
			if($i === 30) break;

		}

		return $timestamp;
	}

	/**
	 * 今日の日付が定休日であるか調べる
	 * @param int timestamp
	 * @return bool
	 */
	function checkRegularHoliday(int $timestamp){
		//プラグインが有効でない場合はfalseを返して処理を停止する
		if(!SOYShopPluginUtil::checkIsActive("parts_calendar")) return false;
		
		$w = date("w", $timestamp);
		$dateArray = explode("-", date("Y-n-j", $timestamp));
		$ymd = $dateArray[0] . "/" . $dateArray[1] . "/"  . $dateArray[2];
		$md = $dateArray[1] . "/"  . $dateArray[2];

		//強制的な営業日でないか調べる 最初に調べる
		foreach(PartsCalendarCommon::getBDConfig() as $cnf){
			if($ymd == $cnf || $ymd == self::_convertDateNotation($cnf)) return false;
		}

		//定期的な定休日
		if(in_array($w, PartsCalendarCommon::getWeekConfig())) return true;

		//週ごとの曜日の定休日
		$cnf = PartsCalendarCommon::getDayOfWeekConfig();
		$nth = ((int)$dateArray[2] - 1) / 7 + 1;
		$nth = (int)$nth;
		if(isset($cnf[$nth]) && in_array($w, $cnf[$nth])) return true;

		//MM/DD形式の定休日を調べる
		foreach(PartsCalendarCommon::getMdConfig() as $cnf){
			if($md == $cnf || $md == self::_convertDateNotation($cnf)) return true;
		}
			
		//YYYY/MM/DD形式で定休日を調べる
		foreach(PartsCalendarCommon::getYmdConfig() as $cnf){
			if($ymd == $cnf || $ymd == self::_convertDateNotation($cnf)) return true;
		}

		// その他
		foreach(PartsCalendarCommon::getOtherConfig() as $cnf){
			if($ymd == $cnf || $ymd == self::_convertDateNotation($cnf)) return false;
		}

		return false;
	}

	/**
	 * @param string
	 * @return string
	 */
	private function _convertDateNotation(string $v){
		if(strpos($v, "/") === false) return $v;
		$values = explode("/", $v);
		for($i = 0; $i < count($values); $i++){
			$values[$i] = (int)$values[$i];
		}
		return implode("/", $values);
	}
}
