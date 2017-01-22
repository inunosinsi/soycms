<?php

class ReserveCalendarUtil{
	
/* sync customfield config */
	const DELIVERY_TWO_DAYS = "1～2営業日";
	const DELIVERY_FOUR_DAYS = "3～4営業日";
	const DELIVERY_ONE_WEEK = "1週間以降";
	const DELIVERY_TWO_WEEK = "2週間以降";
	const DELIVERY_THREE_WEEK = "3週間以降";
	const DELIVERY_ONE_MONTH = "1ヶ月以降";
	const DELIVERY_TWO_MONTH = "2ヶ月以降";
	const DELIVERY_BACK_ORDER = "お取り寄せ";
	
	private $baseDate;
	
	public static function getAutoConfig($itemId){
		$v = SOYShop_DataSets::get("reserve_calendar.auto_" . $itemId, array(
			"register" => 0,
			"seat" => 0
		));
		
		return $v;
	}
	
	public static function saveAutoConfig($itemId, $values){
		SOYShop_DataSets::put("reserve_calendar.auto_" . $itemId, $values);
	}
	
	public static function getWeekConfig($itemId){
		return SOYShop_DataSets::get("reserve_calendar.week_" . $itemId, array(0, 6));
	}
	public static function saveWeekConfig($itemId, $values){
		SOYShop_DataSets::put("reserve_calendar.week_" . $itemId, $values);
	}

	public static function getWeek(){
		$name = array("sun","mon","tue","wed","thu","fri","sat");
		$jp = array("日曜","月曜","火曜","水曜","木曜","金曜","土曜");
		$week = array();
		for($i= 0; $i < 7; $i++){
			$week[] = array("name" => $name[$i], "jp" => $jp[$i]);
		}
		
		return $week;
		
	}

	public static function getDayOfWeekConfig($itemId){

		$dow = SOYShop_DataSets::get("reserve_calendar.day_of_week_" . $itemId, array());
		if(!count($dow)){
			for($i = 1; $i < 6; $i++){
				$dow[$i] = array();
			}
		}

		return $dow;
	}
	
	public static function saveDayOfWeekConfig($itemId, $values){
		SOYShop_DataSets::put("reserve_calendar.day_of_week_" . $itemId, $values);
	}
	
	/**
	 * 月日での設定
	 */
	public static function getMdConfig($itemId, $isText = false){
		$config = SOYShop_DataSets::get("reserve_calendar.md_" . $itemId, array());
		if($isText) $config = implode("\n", $config);
		
		return $config;
	}
	
	public static function saveMdConfig($itemId, $values){
		SOYShop_DataSets::put("reserve_calendar.md_" . $itemId, $values);
	}
	
	/**
	 * 年月日での設定
	 */
	public static function getYmdConfig($itemId, $isText = false){
		$config = SOYShop_DataSets::get("reserve_calendar.ymd_" . $itemId, array());
		if($isText)$config = implode("\n", $config);
		
		return $config;
	}
	
	public static function saveYmdConfig($itemId, $values){
		SOYShop_DataSets::put("reserve_calendar.ymd_" . $itemId, $values);
	}
	
	/**
	 * 営業日
	 */
	public static function getBDConfig($itemId, $isText = false){
		$config = SOYShop_DataSets::get("reserve_calendar.business_day_" . $itemId, array());
		if($isText)$config = implode("\n", $config);

		return $config;
	}
	
	public static function saveBDConfig($itemId, $values){
		SOYShop_DataSets::put("reserve_calendar.business_day_" . $itemId, $values);
	}
	
	/**
	 * その他の日
	 */
	public static function getOtherConfig($itemId, $isText = false){
		$config = SOYShop_DataSets::get("reserve_calendar.other_day_" . $itemId, array());
		if($isText)$config = implode("\n", $config);

		return $config;
	}
	
	public static function saveOtherConfig($itemId, $values){
		SOYShop_DataSets::put("reserve_calendar.other_day_" . $itemId, $values);
	}
	
	/** セッション **/
	public static function getSessionValue($key){
		$session = SOY2ActionSession::getUserSession();
		return $session->getAttribute("reserve_calender_session_" . $key);
	}
	
	public static function saveSessionValue($key, $value){
		$session = SOY2ActionSession::getUserSession();
		return $session->setAttribute("reserve_calender_session_" . $key, $value);
	}
}
?>