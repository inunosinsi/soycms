<?php

class PartsCalendarCommon{
	
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
	
	public static function getWeekConfig(){
		return SOYShop_DataSets::get("calendar.config.week", array(0, 6));
	}

	public static function getWeek(){
		$name = array("sun","mon","tue","wed","thu","fri","sat");
		$jp = array("日曜","月曜","火曜","水曜","木曜","金曜","土曜");
		$week = array();
		for($i=0;$i<7;$i++){
			$week[] = array("name"=>$name[$i],"jp"=>$jp[$i]);
		}
		
		return $week;
		
	}

	public static function getDayOfWeekConfig(){

		try{
			$dow = SOYShop_DataSets::get("calendar.config.day_of_week");
		}catch(Exception $e){
			$dow = array();	//default
			for($i = 1; $i < 6; $i++){
				$dow[$i] = array();
			}
		}
		return $dow;
	}
	
	/**
	 * 月日での設定
	 */
	public static function getMdConfig($isText=false){
		try{
			$config = SOYShop_DataSets::get("calendar.config.md", array());
			if($isText) $config = implode("\n", $config);
		}catch(Exception $e){
			$config = array();
		}
		return $config;
	}
	
	/**
	 * 年月日での設定
	 */
	public static function getYmdConfig($isText=false){
		try{
			$config = SOYShop_DataSets::get("calendar.config.ymd", array());
			if($isText)$config = implode("\n", $config);
		}catch(Exception $e){

		}
		return $config;
	}
	
	/**
	 * 営業日
	 */
	public static function getBDConfig($isText=false){
		try{
			$config = SOYShop_DataSets::get("calendar.config.business_day", array());
			if($isText)$config = implode("\n", $config);
		}catch(Exception $e){

		}
		return $config;
	}
	
	/**
	 * その他の日
	 */
	public static function getOtherConfig($isText=false){
		try{
			$config = SOYShop_DataSets::get("calendar.config.other_day", array());
			if($isText)$config = implode("\n", $config);
		}catch(Exception $e){

		}
		return $config;
	}
}
?>