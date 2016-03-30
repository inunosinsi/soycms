<?php

class CalendarLogic extends SOY2LogicBase{
	
	function CalendarLogic(){
		SOY2::import("module.plugins.parts_calendar.common.PartsCalendarCommon");
	}
	
	function isBD($time){
		return $this->calendarIsBD($time);
	}
	
	function isOther($time){		
		$other = PartsCalendarCommon::getOtherConfig();
		$date = date("Y/m/d", $time);
		return (in_array($date, $other));	
	}
	
	/**
	 * 営業日での計算
	 */
	function BDCaluculate($day, $base){
		
		$bd = 0;
		//今日の午前0時
		
		$time = strtotime(date("Y/m/d", $base));
		$add = 60 * 60 * 24;
		
		while($bd <= $day){
			if($this->calendarIsBD($time)){
				//businessday
				$bd++;
			}
			$time += $add;
		}
		$send_day = date("Y/m/d", $time);		
		$send_time = strtotime($send_day);
		
		return $send_time;
	}
	
	/**
	 * 営業日の判定
	 * @return boolean
	 */
	function calendarIsBD($time){
		$res = true;
		
		//@TODO 毎週X曜日が休みの判定
		if($this->EveryWeekHoliday($time)){
			$res = false;	
		}
		
		//@TODO 第n週のX曜日が休みの判定
		if($this->NthDayHoliday($time)){
			$res = false;	
		}
		
		//@TODO 指定月日が休みの判定
		if($this->MdHoliday($time)){
			$res = false;	
		}

		//@TODO 指定年月日が休みの判定
		if($this->YmdHoliday($time)){
			$res = false;	
		}
		
		//@TODO 指定営業日
		if($this->Businessday($time)){
			$res = true;
		}
		
		//@TODO 指定営業日
		if($this->isOther($time)){
			$res = true;
		}
		
		return $res;
	}
	
	/**
	 * 毎週X曜日が休み
	 */
	function EveryWeekHoliday($time){
		// Sun, Sat
		$yobi = PartsCalendarCommon::getWeekConfig();
		return (in_array(date("w", $time), $yobi));
	}
	
	/**
	 * 第n週のX曜日が休みの判定
	 */
	function NthDayHoliday($time){
		$holidays = PartsCalendarCommon::getDayOfWeekConfig();
		if(count($holidays) == 0) return false;
		
		$DOW = date("w", $time);
		$day = date("d", $time);

		$nth = $day / 7 + 1;
		$nth = (integer)$nth;
		
		//週
		if(array_key_exists($nth, $holidays)){
			if(in_array($DOW, $holidays[$nth])) return true;
		}
		
		return false;
	}
	
	/**
	 * 指定月日が休みの判定
	 */
	function MdHoliday($time){

		$holidays = PartsCalendarCommon::getMdConfig();
		
		$date = date("m/d", $time);
		return (in_array($date, $holidays));
	}
	
	/**
	 * 指定年月日が休みの判定
	 */
	function YmdHoliday($time){

		$holidays = PartsCalendarCommon::getYmdConfig();
		$date = date("Y/m/d", $time);
		return (in_array($date, $holidays));
	}
	
	/**
	 * 指定営業日
	 */
	function Businessday($time){

		$businessdays = PartsCalendarCommon::getBDConfig();
		$date = date("Y/m/d", $time);
		return (in_array($date, $businessdays));
	}	
	
	
	/**
	 * 発送日5日間のセレクトボックスを作成
	 */
	function getOptions($send_time){
		
		$options = array();
		$options[] = date("Y年m月d日", $send_time);
		$count = 0;

		while(count($options) < 5){
			$send_time += 60 * 60 * 24;
			if($this->CalendarIsDB($send_time)){
				$options[]  = date("Y年m月d日", $send_time);
			}
			$count++;
			if($count > 100) break;
		}
		return $options;
	}
	
	/**
	 * 発送予定日初日を取得
	 */
	public function getSendDate($span){
		
		$base = strtotime($this->getBaseDate());
		$date = date("Y/m/d");

		switch($span){
			case self::DELIVERY_TWO_DAYS:
				//2営業日後
				$date = $this->BDCaluculate(2, $base);
				$date = date("Y/m/d", $date);
				break;
			
			case self::DELIVERY_FOUR_DAYS:
				//4営業日後
				$date = $this->BDCaluculate(4, $base);
				$date = date("Y/m/d", $date);
				break;
			
			case self::DELIVERY_ONE_WEEK:
				//1週間後
				$base += 60 * 60 * 24 * 7;
				$date = date("Y/m/d", $base);
				break;
			
			case self::DELIVERY_TWO_WEEK:
				//2週間後
				$base += 60 * 60 * 24 * 14;
				$date = date("Y/m/d", $base);
				break;
			
			case self::DELIVERY_THREE_WEEK:
				//3週間後
				$base += 60 * 60 * 24 * 21;
				$date = date("Y/m/d", $base);
				break;
			
			case self::DELIVERY_ONE_MONTH:
				//１ヶ月後
				
				//その月の末日
				$target = mktime(0, 0, 0, date("m", $base) + 2, 0, date("Y", $base));
				$last = date("d", $target);
				
				//末日調整
				$day = date("d", $base);
				if($last < $day) $day = $last;
				
				//日付取得
				$targetTime = mktime(0, 0, 0, date("m", $base) + 1, $day, date("Y", $base));
				$date = date("Y/m/d", $targetTime);
				break;
			
			case self::DELIVERY_TWO_MONTH:
				//2ヵ月後
				
				//その月の末日
				$target = mktime(0, 0, 0, date("m", $base) + 3, 0, date("Y", $base));
				$last = date("d", $target);
				
				//末日調整
				$day = date("d", $base);
				if($last < $day) $day = $last;
				
				//日付取得
				$targetTime = mktime(0, 0, 0, date("m", $base) + 2, $day, date("Y", $base));
				$date = date("Y/m/d", $targetTime);
				break;
			
			case self::DELIVERY_BACK_ORDER:
				//取り寄せ
				default:
				return self::DELIVERY_BACK_ORDER;
		}
		
		//発送日が発送可能か
		$time = strtotime($date);

		while(!$this->CalendarIsDB($time)){
			$time += 60 * 60 * 24;//翌日へ
		}
		
		return $time;
	}

	function getBaseDate() {
		return $this->baseDate;
	}
	function setBaseDate($baseDate) {
		$this->baseDate = $baseDate;
	}
}
?>