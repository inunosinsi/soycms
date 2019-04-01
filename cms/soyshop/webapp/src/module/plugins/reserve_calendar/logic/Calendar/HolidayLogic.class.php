<?php

class HolidayLogic extends SOY2LogicBase{

	private $itemId;

	function __construct(){
		SOY2::import("module.plugins.reserve_calendar.util.ReserveCalendarUtil");
	}

	//指定した年月の日数
	function getDayCount($y, $m){
		return (int)date("d", strtotime("+1 month", mktime(0, 0, 0, $m, 1, $y)) - 1);
	}

	function isBD($time){
		return self::calendarIsBD($time);
	}

	/**
	 * 営業日の判定
	 * @return boolean
	 */
	private function calendarIsBD($time){
		$res = true;

		//@TODO 毎週X曜日が休みの判定
		if(self::EveryWeekHoliday($time)) $res = false;

		//@TODO 第n週のX曜日が休みの判定
		if(self::NthDayHoliday($time)) $res = false;

		//@TODO 指定月日が休みの判定
		if(self::MdHoliday($time)) $res = false;

		//@TODO 指定年月日が休みの判定
		if(self::YmdHoliday($time)) $res = false;

		//@TODO 指定営業日
		if(self::Businessday($time)) $res = true;

		//@TODO 指定営業日
		if(self::isOther($time)) $res = true;

		return $res;
	}

	/**
	 * 毎週X曜日が休み
	 */
	private function EveryWeekHoliday($time){
		static $yobi;
		if(is_null($yobi)) $yobi = ReserveCalendarUtil::getWeekConfig($this->itemId);
		return (in_array(date("w", $time), $yobi));
	}

	/**
	 * 第n週のX曜日が休みの判定
	 */
	private function NthDayHoliday($time){
		static $holidays;
		if(is_null($holidays)) $holidays = ReserveCalendarUtil::getDayOfWeekConfig($this->itemId);
		if(count($holidays) == 0) return false;

		$DOW = date("w", $time);
		$day = (int)date("d", $time);

		$nth = ($day - 1) / 7 + 1;
		$nth = (int)$nth;

		//週
		if(array_key_exists($nth, $holidays)){
			if(in_array($DOW, $holidays[$nth])) return true;
		}

		return false;
	}

	/**
	 * 指定月日が休みの判定
	 */
	private function MdHoliday($time){
		static $holidays;
		if(is_null($holidays)) $holidays = ReserveCalendarUtil::getMdConfig($this->itemId);

		$date = date("m/d", $time);
		return (in_array($date, $holidays));
	}

	/**
	 * 指定年月日が休みの判定
	 */
	private function YmdHoliday($time){
		static $holidays;
		if(is_null($holidays)) $holidays = ReserveCalendarUtil::getYmdConfig($this->itemId);
		$date = date("Y/m/d", $time);
		return (in_array($date, $holidays));
	}

	/**
	 * 指定営業日
	 */
	private function Businessday($time){
		static $businessdays;
		if(is_null($businessdays)) $businessdays = ReserveCalendarUtil::getBDConfig($this->itemId);
		$date = date("Y/m/d", $time);
		return (in_array($date, $businessdays));
	}

	private function isOther($time){
		static $other;
		if(is_null($other)) $other = ReserveCalendarUtil::getOtherConfig($this->itemId);
		$date = date("Y/m/d", $time);
		return (in_array($date, $other));
	}

	function setItemId($itemId){
		$this->itemId = $itemId;
	}
}
