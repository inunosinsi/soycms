<?php

SOY2::import("module.plugins.reserve_calendar.component.base.CalendarBaseComponent");
class CalendarLogic extends CalendarBaseComponent{

	private $year;
	private $month;
	private $itemId;
	private $sync;
	private $isPublished = true;

	private $labelList;

	private $addedList = array();

	private $config;

	function build(int $y, int $m, bool $dspOtherMD=true, bool $dspCaption=false, bool $dspRegHol=true, bool $dspMonthLink=false, bool $isBefore=true, bool $isNextMonth=false, int $addMonth=1){
		$commentTag = "<!-- output calendar plugin -->";
		if(!$this->isPublished) return $commentTag;

		$this->year = $y;
		$this->month = $m;

		$this->labelList = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Calendar.LabelLogic")->getLabelList($this->itemId);

		SOY2::import("module.plugins.reserve_calendar.util.ReserveCalendarUtil");
		SOY2::import("module.plugins.calendar_expand_seat.util.ExpandSeatUtil");
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

		return $commentTag."\n".parent::build($y, $m, $dspOtherMD, $dspCaption, $dspRegHol, $dspMonthLink, $isBefore, $isNextMonth);
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
		if(is_numeric($isOtherMonth)) $isOtherMonth = true;
		$sch = self::getScheduleArray($i);

		$html = array();
		$html[] = "<i>" . $i . "</i>";

		//予定がある場合
		if(count($sch)){
			foreach($sch as $schId => $v){

				//残席があるか調べる
				if(!$isOtherMonth && self::checkIsUnsoldSeat($i, $schId, $v["seat"])){
					$label = self::getLabel($v["label_id"]);

					//ラベル名から表示するか決める
					if(ReserveCalendarUtil::checkLabelString($label, $this->year, $this->month, $i)){
						$html[] = "<a href=\"javascript:void(0);\" class=\"btn btn-primary schedule_button\" onclick=\"insert_schedule_form(this, " . $schId . ");\">" . $label . "</a>";

						//価格の表示
						if(isset($v["price"]) && isset($this->config["show_price"]) && $this->config["show_price"] == ReserveCalendarUtil::IS_SHOW){
							$html[] = number_format($v["price"]) . "円";

							//子供料金
							$childPrice = ExpandSeatUtil::getChildPrice($schId);
							if(isset($childPrice) && is_numeric($childPrice) && $v["price"] != $childPrice){
								$html[] = "子 " . number_format($childPrice) . "円";
							}
						}
					}
				//残席がなければ、今のところ何もしない
				}else{

				}
			}
		}

		return implode("<br>", $html);
	}

	private function checkIsUnsoldSeat(int $d, int $schId, int $seat){
		//今日よりも前の日の場合は残席数は0になる
		$schDate = mktime(0, 0, 0, $this->month, $d, $this->year) + 24 * 60 * 60 - 1;
		if($schDate < time()) return false;

		// 受付期限
		if(isset($this->config["deadline"]) && is_numeric($this->config["deadline"]) && $this->config["deadline"] > 0) {
			if($schDate <= soyshop_shape_timestamp(strtotime("+ " . $this->config["deadline"] . "day"))){
				return false;
			}
		}

		//すでにカートに入れてないか？予約一件のみモードの場合はチェックしない
		if(!self::isOnly() && in_array($schId, $this->addedList)) return false;

		//予約がなければ必ずtrue
		if(!isset($GLOBALS["reserved_schedules"][$schId])) return true;

		//残席数と比較する
		return ($GLOBALS["reserved_schedules"][$schId] < $seat);
	}

	private function getScheduleArray($d){
		return (isset($GLOBALS["schedules"][$d])) ? $GLOBALS["schedules"][$d] : array();
	}

	private function getLabel($labelId){
		return (isset($this->labelList[$labelId])) ? $this->labelList[$labelId] : "";
	}

	//カートに入れるプランは一件のみモードであるか？
	private function isOnly(){
		static $isOnly;
		if(is_null($isOnly)){
			SOY2::import("module.plugins.reserve_calendar.util.ReserveCalendarUtil");
			$config = ReserveCalendarUtil::getConfig();
			$isOnly = (isset($config["only"]) && (int)$config["only"] === 1);
		}
		return $isOnly;
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
