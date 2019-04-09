<?php

SOY2::import("module.plugins.reserve_calendar.component.base.CalendarBaseComponent");
class CalendarLogic extends CalendarBaseComponent{

	private $year;
	private $month;
	private $itemId;

	function build($y, $m, $dspOtherMD = false, $dspCaption = true, $dspRegHol = true, $dspMonthLink = false){
		$this->year = $y;
		$this->month = $m;

		return parent::build($y, $m, $dspOtherMD, $dspCaption, $dspRegHol, $dspMonthLink);
	}

	/**
	 * @final
	 */
	function getRegularHolidayList(){
		$array = array();

		//indexに月、配列に各日を突っ込む
		$logic = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Calendar.HolidayLogic", array("itemId" => $this->itemId));
		$days = $logic->getDayCount($this->year, $this->month); //今月の日数

		for($i = 1; $i <= $days; $i++){
			if(!$logic->isBD(mktime(0, 0, 0, $this->month, $i, $this->year))) $array[] = $i;
		}

		parent::setRegularHolidays(array($this->month => $array));
	}

	function handleFunc($i, $cd, $wc, $da, $isOtherMonth){
		$sch = self::getScheduleArray($i);

		$html = array();

		$html[] = $i;

		//予定がある場合
		if(count($sch)){
			foreach($sch as $schId => $v){
				$html[] = "<label><input type=\"checkbox\" name=\"Schedule[" . $schId . "]\" value=\"1\">" . self::getLabel($v["label_id"]) . " " . $v["seat"] . "</label><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . number_format($v["price"]) . "円";
			}
		}

		//定休日に追加のチェックボックスを追加しない
		if($da != "reg"){
			$html[] = "<label><input type=\"checkbox\" name=\"column[" . $i . "]\" value=\"1\"> <strong>追加</strong></label>";
		}
		return implode("<br>", $html);
	}

	private function getLabel($labelId){
		return (isset($GLOBALS["labelList"][$labelId])) ? $GLOBALS["labelList"][$labelId] : "";
	}

	private function getScheduleArray($d){
		return (isset($GLOBALS["scheduleList"][$d])) ? $GLOBALS["scheduleList"][$d] : array();
	}

	function setItemId($itemId){
		$this->itemId = $itemId;
	}
}
