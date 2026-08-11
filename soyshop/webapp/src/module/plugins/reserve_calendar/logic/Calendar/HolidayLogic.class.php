<?php

class HolidayLogic extends SOY2LogicBase{

	private $itemId;

	function __construct(){
		SOY2::import("module.plugins.reserve_calendar.util.ReserveCalendarUtil");
	}

	//指定した年月の日数
	function getDayCount(int $y, int $m){
		return (int)date("d", strtotime("+1 month", mktime(0, 0, 0, $m, 1, $y)) - 1);
	}

	function isBD(int $time){
		return self::_calendarIsBD($time);
	}

	/**
	 * 営業日の判定
	 * @param int
	 * @return boolean
	 */
	private function _calendarIsBD(int $time){
		$res = true;

		//@TODO 毎週X曜日が休みの判定
		if(self::_EveryWeekHoliday($time)) $res = false;

		//@TODO 第n週のX曜日が休みの判定
		if(self::_NthDayHoliday($time)) $res = false;

		//@TODO 指定月日が休みの判定
		if(self::_MdHoliday($time)) $res = false;
		
		//@TODO 指定年月日が休みの判定
		if(self::_YmdHoliday($time)) $res = false;

		//@TODO 指定営業日
		if(self::_Businessday($time)) $res = true;

		//@TODO 指定営業日
		if(self::_isOther($time)) $res = true;

		return $res;
	}

	/**
	 * 毎週X曜日が休み
	 */
	private function _EveryWeekHoliday(int $time){
		static $yobi;
		if(!is_numeric($this->itemId)) return null;
		if(!is_array($yobi)) $yobi = array();
		if(!isset($yobi[$this->itemId])) $yobi[$this->itemId] = ReserveCalendarUtil::getWeekConfig($this->itemId);
		return (in_array(date("w", $time), $yobi[$this->itemId]));
	}

	/**
	 * 第n週のX曜日が休みの判定
	 */
	private function _NthDayHoliday(int $time){
		static $holidays;
		if(!is_numeric($this->itemId)) return null;
		if(!is_array($holidays)) $holidays = array();
		if(!isset($holidays[$this->itemId])) $holidays[$this->itemId] = ReserveCalendarUtil::getDayOfWeekConfig($this->itemId);
		if(count($holidays[$this->itemId]) == 0) return false;

		$DOW = date("w", $time);
		$day = (int)date("d", $time);

		$nth = ($day - 1) / 7 + 1;
		$nth = (int)$nth;

		//週
		if(array_key_exists($nth, $holidays[$this->itemId])){
			if(in_array($DOW, $holidays[$this->itemId][$nth])) return true;
		}

		return false;
	}

	/**
	 * 指定月日が休みの判定
	 */
	private function _MdHoliday(int $time){
		static $holidays;
		if(!is_numeric($this->itemId)) return null;
		if(!is_array($holidays)) $holidays = array();
		if(!isset($holidays[$this->itemId])) $holidays[$this->itemId] = ReserveCalendarUtil::getMdConfig($this->itemId);

		$date = date("m/d", $time);
		return (in_array($date, $holidays[$this->itemId]));
	}

	/**
	 * 指定年月日が休みの判定
	 */
	private function _YmdHoliday(int $time){
		static $holidays;
		if(!is_numeric($this->itemId)) return null;
		if(!is_array($holidays)) $holidays = array();
		if(!isset($holidays[$this->itemId])) $holidays[$this->itemId] = ReserveCalendarUtil::getYmdConfig($this->itemId);
		$date = date("Y/m/d", $time);
		return (in_array($date, $holidays[$this->itemId]));
	}

	/**
	 * 指定営業日
	 */
	private function _Businessday(int $time){
		static $businessdays;
		if(!is_numeric($this->itemId)) return null;
		if(!is_array($businessdays)) $businessdays = array();
		if(!isset($businessdays[$this->itemId])) $businessdays[$this->itemId] = ReserveCalendarUtil::getBDConfig($this->itemId);
		$date = date("Y/m/d", $time);
		return (in_array($date, $businessdays[$this->itemId]));
	}

	private function _isOther(int $time){
		static $other;
		if(!is_numeric($this->itemId)) return null;
		if(!is_array($other)) $other = array();
		if(!isset($other[$this->itemId])) $other[$this->itemId] = ReserveCalendarUtil::getOtherConfig($this->itemId);
		$date = date("Y/m/d", $time);
		return (in_array($date, $other[$this->itemId]));
	}

	function setItemId($itemId){
		$this->itemId = $itemId;
	}
}
