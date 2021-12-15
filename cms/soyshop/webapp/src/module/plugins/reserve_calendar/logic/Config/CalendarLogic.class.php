<?php

SOY2::import("module.plugins.reserve_calendar.component.base.CalendarBaseComponent");
class CalendarLogic extends CalendarBaseComponent{

	private $year;
	private $month;
	private $itemId;
	private $extPrices = array();

	function build(int $y, int $m, bool $dspOtherMD=false, bool $dspCaption=true, bool $dspRegHol=true, bool $dspMonthLink=false, bool $isBefore=false, bool $isNextMonth=false){
		$this->year = $y;
		$this->month = $m;

		//金額の拡張があるか？調べる
		SOY2::import("module.plugins.reserve_calendar.domain.SOYShopReserveCalendar_SchedulePriceDAO");
		$this->extPrices = SOY2DAOFactory::create("SOYShopReserveCalendar_SchedulePriceDAO")->getPriceListByYearAndMonth($y, $m);

		return parent::build($y, $m, $dspOtherMD, $dspCaption, $dspRegHol, $dspMonthLink, $isBefore, $isNextMonth);
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

	function handleFunc(int $i, int $cd, int $wc, string $da, bool $isOtherMonth){
		$sch = self::getScheduleArray($i);

		$html = array();

		$html[] = $i;

		//予定がある場合
		if(count($sch)){
			foreach($sch as $schId => $v){
				$f = array();
				$f[] = "<label>";
				$f[] = "<input type=\"checkbox\" name=\"Schedule[" . $schId . "]\" value=\"1\">";
				$f[] = self::getLabel($v["label_id"]) . " " . $v["seat"];
				$f[] = "</label>";
				$f[] = "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . number_format($v["price"]) . "円";

				if(isset($this->extPrices[$schId]) && count($this->extPrices[$schId])){
					foreach($this->extPrices[$schId] as $fieldId => $values){
						$f[] = "<br>&nbsp;&nbsp;&nbsp;&nbsp;" . mb_substr($values["label"], 0, 1) . number_format($values["price"]) . "円";
					}
				}

				//拡張の金額があるか？調べる

				$html[] = implode("", $f);
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
