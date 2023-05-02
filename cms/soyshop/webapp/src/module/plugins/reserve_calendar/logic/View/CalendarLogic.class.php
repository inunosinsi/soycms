<?php

SOY2::import("module.plugins.reserve_calendar.component.base.CalendarBaseComponent");
class CalendarLogic extends CalendarBaseComponent{

	private $year;
	private $month;
	private $itemId;
	private $sync;
	private $isPublished = true;

	private $schList;
	private $labelList;

	private $addedList = array();
	private $config;

	function build(int $y, int $m, bool $dspOtherMD=false, bool $dspCaption=true, bool $dspRegHol=true, bool $dspMonthLink=false, bool $isBefore=false, bool $isNextMonth=false, int $addMonth=1){
		if(!$this->isPublished) return "";
		$this->year = $y;
		$this->month = $m;

		$this->schList = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Schedule.ScheduleLogic")->getScheduleList($this->itemId, $y, $m);
		$this->labelList = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Calendar.LabelLogic")->getLabelList($this->itemId);

		SOY2::import("module.plugins.reserve_calendar.util.ReserveCalendarUtil");
		$this->config = ReserveCalendarUtil::getConfig();

		//カートに入っているスケジュールを取得する
		$cart = CartLogic::getCart();
		$items = $cart->getItems();
		if(count($items)){
			foreach($items as $idx => $item){
				$schId = $cart->getAttribute("reserve_calendar_schedule_id_" . $idx . "_" . $item->getItemId());
				if(isset($schId)) $this->addedList[] = $schId;
			}
		}

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
				//受付期限内であるか？
				if(!self::checkWithinReceptionDeadline($i)) continue;

				//残席があるか調べる
				if(self::checkIsUnsoldSeat($i, $schId, $v["seat"])){
					$label = self::getLabel($v["label_id"]);

					//ラベル名から表示するか決める
					if(ReserveCalendarUtil::checkLabelString($label, $this->year, $this->month, $i)){
						if($this->sync){
							$html[] = "<a href=\"" . soyshop_get_cart_url(true) . "?a=add&schId=" . $schId . "\" id=\"" . $this->year . "-" . $this->month . "-" . $i . "\"><button id=\"" . $this->year . "-" . $this->month . "-" . $i . "_button\">" . $label . "</button></a>";
							//$html[] = "<button onclick=\"location.href='" . soyshop_get_cart_url(true) . "?a=add&schId=" . $schId . "'\" id=\"" . $this->year . "-" . $this->month . "-" . $i . "\">" . $label . "</button>";
						//非同期ボタン
						}else{
							$html[] = "<button id=\"reserve_calendar_async_button_" . $schId . "\" onclick=\"AsyncReserveCalendar.add(this," . $schId . ");\">" . $label . "</button>";
						}

						//価格の表示
						if(isset($v["price"]) && isset($this->config["show_price"]) && $this->config["show_price"] == ReserveCalendarUtil::IS_SHOW){
							$html[] = number_format($v["price"]) . "円";
						}
					}

				//残席がなければ、今のところ何もしない
				}else{

				}
			}
		}

		return implode("<br>", $html);
	}

	private function checkWithinReceptionDeadline(int $d){
		//受付期限設定がない、もしくは期限設定が0日の場合は必ずtrue
		if(!isset($this->config["deadline"]) || !is_numeric($this->config["deadline"]) || $this->config["deadline"] == 0) return true;
		return (mktime(0, 0, 0, $this->month, $d, $this->year) >= soyshop_shape_timestamp(strtotime("+ " . $this->config["deadline"] . "day")));
	}

	private function checkIsUnsoldSeat(int $d, int $schId, int $seat){
		//今日よりも前の日の場合は残席数は0になる
		$schDate = mktime(0, 0, 0, $this->month, $d, $this->year) + 24 * 60 * 60 - 1;
		if($schDate < time()) return false;

		//すでにカートに入れてないか？
		if(in_array($schId, $this->addedList)) return false;

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
	function setSync($sync){
		$this->sync = $sync;
	}
	function setIsPublished(bool $isPublished){
		$this->isPublished = $isPublished;
	}
}
