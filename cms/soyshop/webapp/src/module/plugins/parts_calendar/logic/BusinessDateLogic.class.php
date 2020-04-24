<?php

class BusinessDateLogic extends SOY2LogicBase{

	function __construct(){
		SOY2::import("util.SOYShopPluginUtil");
		SOY2::import("module.plugins.parts_calendar.common.PartsCalendarCommon");
	}

	//次の営業日を調べる
	function getNextBusinessDate($i = 0){

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

	//今日の日付が定休日であるか調べる
	function checkRegularHoliday($timestamp){
		$flag = false;

		//本日が定休日であるか調べる
		if(SOYShopPluginUtil::checkIsActive("parts_calendar")){
			$w = date("w", $timestamp);
			$dateArray = explode("-", date("Y-n-j", $timestamp));


			//定期的な定休日
			if(in_array($w, PartsCalendarCommon::getWeekConfig())) $flag = true;

			//週ごとの曜日の定休日
			if(!$flag){
				$conf = PartsCalendarCommon::getDayOfWeekConfig();
				$nth = ((int)$dateArray[2] - 1) / 7 + 1;
				$nth = (int)$nth;
				if(isset($conf[$nth]) && in_array($w, $conf[$nth])){
					$flag = true;
				}
			}

			//MM/DD形式の定休日を調べる
			if(!$flag){
				foreach(PartsCalendarCommon::getMdConfig() as $conf){
					$d = $dateArray[1] . "/"  . $dateArray[2];
					if($d == $conf || $d == self::_convertDateNotation($conf)){
						$flag = true;
						break;
					}
				}
			}

			//YYYY/MM/DD形式で定休日を調べる
			if(!$flag){
				foreach(PartsCalendarCommon::getYmdConfig() as $conf){
					$d = $dateArray[0] . "/" . $dateArray[1] . "/"  . $dateArray[2];
					if($d == $conf || $d == self::_convertDateNotation($conf)){
						$flag = true;
						break;
					}
				}
			}

			//強制的な営業日でないか調べる
			if($flag){
				foreach(PartsCalendarCommon::getBDConfig() as $conf){
					$d = $dateArray[0] . "/" . $dateArray[1] . "/"  . $dateArray[2];
					if($d == $conf || $d == self::_convertDateNotation($conf)){
						$flag = false;
						break;
					}
				}

				if($flag){
					foreach(PartsCalendarCommon::getOtherConfig() as $conf){
						$d = $dateArray[0] . "/" . $dateArray[1] . "/"  . $dateArray[2];
						if($d == $conf || $d == self::_convertDateNotation($conf)){
							$flag = false;
							break;
						}
					}
				}
			}
		}

		return $flag;
	}

	private function _convertDateNotation($v){
		if(strpos($v, "/") === false) return $v;
		$values = explode("/", $v);
		for($i = 0; $i < count($values); $i++){
			$values[$i] = (int)$values[$i];
		}
		return implode("/", $values);
	}
}
