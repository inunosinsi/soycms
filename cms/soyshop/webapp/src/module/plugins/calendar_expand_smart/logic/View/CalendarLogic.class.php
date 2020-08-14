<?php

SOY2::import("module.plugins.reserve_calendar.component.base.CalendarBaseComponent");
class CalendarLogic extends CalendarBaseComponent{

	private $year;
	private $month;
	private $itemId;
	private $sync;

	private $schList;
	private $labelList;

	private $addedList = array();

	private $config;

	function build($y, $m, $dspOtherMD = true, $dspCaption = false, $dspRegHol = true, $dspMonthLink = false, $isBefore = true, $isNextMonth = true){
		$this->year = $y;
		$this->month = $m;

		$this->schList = SOY2Logic::createInstance("module.plugins.calendar_expand_smart.logic.Schedule.SmartScheduleLogic")->getScheduleList($this->itemId, $y, $m);
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

		return "<!-- output calendar plugin -->\n" . parent::build($y, $m, $dspOtherMD, $dspCaption, $dspRegHol, $dspMonthLink, $isBefore, $isNextMonth);
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

		$t = mktime(0, 0, 0, $m, $i, $y);	//タイムスタンプを作成

		$sch = self::getScheduleArray($t);

		$html = array();
		$html[] = "<i>" . $i . "</i>";

		//予定がある場合
		if(count($sch)){
			foreach($sch as $schId => $v){

				//残席があるか調べる

				if(!$isForceHidden && self::checkIsUnsoldSeat($t, $schId, $v["seat"])){
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

	private function checkIsUnsoldSeat($schDate, $schId, $seat){
		//今日よりも前の日の場合は残席数は0になる
		if($schDate < strtotime("-1 day")) return false;

		//すでにカートに入れてないか？ @ToDo 簡易予約カレンダーの方の設定に合わせたい
		if(!self::isOnly() && in_array($schId, $this->addedList)) return false;

		//予約がなければ必ずtrue
		if(!isset($GLOBALS["reserved_schedules"][$schId])) return true;

		//残席数と比較する
		return ($GLOBALS["reserved_schedules"][$schId] < $seat);
	}

	private function getScheduleArray($t){
		return (isset($this->schList[$t])) ? $this->schList[$t] : array();
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
}
