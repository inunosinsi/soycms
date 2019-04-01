<?php

SOY2::import("module.plugins.reserve_calendar.component.base.CalendarBaseComponent");
class CalendarLogic extends CalendarBaseComponent{

	private $year;
	private $month;
	private $itemId;

	private $reservedList;
	private $schList;
	private $labelList;

	function build($y, $m, $dspOtherMD = false, $dspCaption = true, $dspRegHol = true, $dspMonthLink = false){
		$this->year = $y;
		$this->month = $m;

		$this->reservedList = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Reserve.ReserveLogic")->getReservedSchedulesByPeriod($y, $m);
		$this->schList = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Schedule.ScheduleLogic")->getScheduleList($this->itemId, $y, $m);
		$this->labelList = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Calendar.LabelLogic")->getLabelList($this->itemId);

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
				$resCnt = self::getReservedCount($schId);
				$seat = (int)$v["seat"];

				$schText = "<a href=\"" . SOY2PageController::createLink("Extension.Detail.reserve_calendar." . $schId) . "\">" . self::getLabel($v["label_id"]) . "  " . $resCnt . "/" . $seat . "</a>";

				//満席
				if($resCnt >= $seat){
					$html[] = "<span class=\"full\">" . $schText . "</span>";
				}else{
					$html[] = $schText;
				}
			}
		}

		return implode("<br>", $html);
	}

	private function getScheduleArray($d){
		return (isset($this->schList[$d])) ? $this->schList[$d] : array();
	}

	private function getLabel($labelId){
		return (isset($this->labelList[$labelId])) ? $this->labelList[$labelId] : "";
	}

	private function getReservedCount($schId){
		return (isset($this->reservedList[$schId])) ? (int)$this->reservedList[$schId] : 0;
	}

	function setItemId($itemId){
		$this->itemId = $itemId;
	}
}
