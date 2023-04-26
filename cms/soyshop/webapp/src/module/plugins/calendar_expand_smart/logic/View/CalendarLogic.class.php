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

	function build(int $y, int $m, bool $dspOtherMD=true, bool $dspCaption=false, bool $dspRegHol=true, bool $dspMonthLink=false, bool $isBefore=true, bool $isNextMonth=true, int $addMonth=1){
		$commentTag = "<!-- output calendar plugin -->";
		if(!$this->isPublished) return $commentTag;
		
		$this->year = $y;
		$this->month = $m;

		SOY2::import("module.plugins.calendar_expand_smart.util.SmartCalendarUtil");
		$d = SmartCalendarUtil::getDisplayDayCount($this->itemId) - date("j");
		$addMonth = ($d > 0) ? (int)($d / 30) + 1 : 1;
		
		$this->schList = SOY2Logic::createInstance("module.plugins.calendar_expand_smart.logic.Schedule.SmartScheduleLogic")->getScheduleList($this->itemId, $y, $m, $addMonth);
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

		SOY2::import("module.plugins.calendar_expand_smart.util.SmartCalendarUtil");
		$pagerCount = SmartCalendarUtil::getPagerDayCount($this->itemId);
		$displayCount = SmartCalendarUtil::getDisplayDayCount($this->itemId);
		
		$n = (is_numeric($pagerCount)) ? $pagerCount : $displayCount;
		$pagerCount = (is_numeric($pagerCount)) ? (int)ceil($displayCount/$pagerCount) : 1;

		$displayCountTag = "<input type=\"hidden\" id=\"reserve_calendar_expand_smart_display_count\" value=\"".$n."\">";
		$pagerCountTag = "<input type=\"hidden\" id=\"reserve_calendar_expand_smart_pager_count\" value=\"".$pagerCount."\">";
		if($pagerCount > 1){	// スマホの時のみ表示するボタン
			$pagerBtn = "<div class=\"text-center mt-3 mb-3 pager_button_area\"><button class=\"btn btn-outline-primary btn-lg\" onclick=\"show_next_page_on_smart_calendar();\">次の日程</button></div>";
			$pagerCountTag .= $pagerBtn;
		}
		return  $commentTag."\n".parent::build($y, $m, $dspOtherMD, $dspCaption, $dspRegHol, $dspMonthLink, $isBefore, $isNextMonth, $addMonth)."\n".$displayCountTag."\n".$pagerCountTag;
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
		$y = $this->year;
		$m = $this->month;

		$isForceHidden = false;	//条件を満たしていてもボタンを表示しない
		if($wc > 4 && date("w", $cd) > 0){	//5週目以降かつ日付では10日以下であれば必ず次の月	ただし日曜日は除く date("w", $cd) > 0で日曜日になる
			if($wc == 5 && $i > 23){
				//何もしない
			}else{
				$m += 1;
				if($m > 12){
					$m -= 12;
					$y += 1;
				}
			}
		}else if($wc == 1 && $i > 24){	//1週目に大きな数字が入っている場合は強席的にボタンは表示しない
			$isForceHidden = true;
		}

		$sch = self::getScheduleArray($cd);

		$html = array();
		$html[] = "<i><object class=\"show-month\">".date("n", $cd)."/</object>" . $i . "</i>";

		//予定がある場合
		if(count($sch)){
			foreach($sch as $schId => $v){

				//残席があるか調べる

				if(!$isForceHidden && self::checkIsUnsoldSeat($cd, $schId, $v["seat"])){
					$label = self::getLabel($v["label_id"]);
					
					//ラベル名から表示するか決める
					if(ReserveCalendarUtil::checkLabelString($label, $y, $m, $i)){
						if($this->sync){
							$html[] = "<a href=\"" . soyshop_get_cart_url(true) . "?a=add&schId=" . $schId . "\" class=\"btn btn-info schedule_button\">" . $label . "</a>";
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

	/**
	 * @param int, int, int
	 * @return bool
	 */
	private function checkIsUnsoldSeat(int $schDate, int $schId, int $seat){
		//今日よりも前の日の場合は残席数は0になる
		if($schDate < strtotime("-1 day")) return false;

		// 受付期限
		if(isset($this->config["deadline"]) && is_numeric($this->config["deadline"]) && $this->config["deadline"] > 0) {
			if($schDate <= soyshop_shape_timestamp(strtotime("+ " . $this->config["deadline"] . "day"))){
				return false;
			}
		}
		

		//すでにカートに入れてないか？ @ToDo 簡易予約カレンダーの方の設定に合わせたい
		if(!self::isOnly() && in_array($schId, $this->addedList)) return false;

		//予約がなければ必ずtrue
		if(!isset($GLOBALS["reserved_schedules"][$schId])) return true;

		//残席数と比較する
		return ($GLOBALS["reserved_schedules"][$schId] < $seat);
	}

	/**
	 * @param int<timestamp>
	 * @return array
	 */
	private function getScheduleArray(int $t){
		return (isset($this->schList[$t])) ? $this->schList[$t] : array();
	}

	/**
	 * @param int
	 * @return string
	 */
	private function getLabel(int $labelId){
		return (isset($this->labelList[$labelId])) ? $this->labelList[$labelId] : "";
	}

	/**
	 * カートに入れるプランは一件のみモードであるか？
	 * @return bool
	 */
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
