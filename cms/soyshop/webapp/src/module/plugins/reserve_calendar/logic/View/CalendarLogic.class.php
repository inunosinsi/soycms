<?php

SOY2::import("module.plugins.reserve_calendar.component.base.CalendarBaseComponent");
class CalendarLogic extends CalendarBaseComponent{
	
	private $year;
	private $month;
	private $itemId;
	
	private $schList;
	private $labelList;
	
	function build($y, $m, $dspOtherMD = false, $dspCaption = true, $dspRegHol = true){
		$this->year = $y;
		$this->month = $m;
		
		$this->schList = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Schedule.ScheduleLogic")->getScheduleList($this->itemId, $y, $m);
		$this->labelList = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Calendar.LabelLogic")->getLabelList($this->itemId);
		
		return parent::build($y, $m, $dspOtherMD, $dspCaption, $dspRegHol);
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
				
				//残席があるか調べる
				if(self::checkIsUnsoldSeat($i, $schId, $v["seat"])){
					$html[] = "<a href=\"" . soyshop_get_cart_url(true) . "?a=add&schId=" . $schId . "\"><button>" . self::getLabel($v["label_id"]) . "</button></a>";

				//残席がなければ、今のところ何もしない
				}else{
					
				}
			}
		}
		
		return implode("<br>", $html);
	}
		
	private function checkIsUnsoldSeat($d, $schId, $seat){
		//今日よりも前の日の場合は残席数は0になる
		$schDate = mktime(0, 0, 0, $this->month, $d, $this->year) + 24 * 60 * 60 - 1;
		if($schDate < time()) return false;
		
		//予約がなければ必ずtrue
		if(!isset($GLOBALS["reserved_schedules"][$schId])) return true;
		
		//残席数と比較する
		return ($GLOBALS["reserved_schedules"][$schId] < $seat);
	}
	
	private function getScheduleArray($d){
		return (isset($this->schList[$d])) ? $this->schList[$d] : array();
	}
	
	private function getLabel($labelId){
		return (isset($this->labelList[$labelId])) ? $this->labelList[$labelId] : "";
	}
	
	function setItemId($itemId){
		$this->itemId = $itemId;
	}
}
?>