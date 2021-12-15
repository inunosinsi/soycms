<?php

SOY2::import("module.plugins.reserve_calendar.component.base.CalendarBaseComponent");
class CalendarLogic extends CalendarBaseComponent{

	private $year;
	private $month;
	private $itemId;
	private $extPrices = array();

	private $reservedList;		//本登録
	private $tmpReservedList;	//仮登録
	private $schList;
	private $labelList;

	function build(int $y, int $m, bool $dspOtherMD=false, bool $dspCaption=true, bool $dspRegHol=true, bool $dspMonthLink=false, bool $isBefore=false, bool $isNextMonth=false){
		$this->year = $y;
		$this->month = $m;

		$resLogic = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Reserve.ReserveLogic");

		$this->reservedList = $resLogic->getReservedSchedulesByPeriod($y, $m);
		$this->tmpReservedList = $resLogic->getReservedSchedulesByPeriod($y, $m, true);
		$this->schList = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Schedule.ScheduleLogic")->getScheduleList((int)$this->itemId, $y, $m);
		$this->labelList = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Calendar.LabelLogic")->getLabelList((int)$this->itemId);

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
				$resCnt = self::getReservedCount($schId);
				$tmpResCnt = self::getTmpReservedCount($schId);
				$seat = (int)$v["seat"];

				$schText = "<a href=\"" . SOY2PageController::createLink("Extension.Detail.reserve_calendar." . $schId) . "\">" . self::getLabel($v["label_id"]) . "  " . $resCnt . "/" . $seat . "</a>";
				if($tmpResCnt > 0) $schText .= " (" . $tmpResCnt . ")";
				if(isset($v["price"]) && is_numeric($v["price"]) && $v["price"] > 0) $schText .= "<br>" . number_format($v["price"]) . "円";

				if(isset($this->extPrices[$schId]) && count($this->extPrices[$schId])){
					foreach($this->extPrices[$schId] as $fieldId => $values){
						$schText .= "<br>" . mb_substr($values["label"], 0, 1) . number_format($values["price"]) . "円";
					}
				}

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

	private function getTmpReservedCount($schId){
		return (isset($this->tmpReservedList[$schId])) ? (int)$this->tmpReservedList[$schId] : 0;
	}

	function setItemId($itemId){
		$this->itemId = $itemId;
	}
}
