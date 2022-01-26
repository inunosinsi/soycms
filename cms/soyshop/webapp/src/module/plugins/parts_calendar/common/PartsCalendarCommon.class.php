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
	
	public static function getWeekConfig(string $base=""){
		if(!strlen($base)) $base = "calendar.config";
		return SOYShop_DataSets::get($base . ".week", array(0, 6));
	}

	/**
	 * @param string key, string|array v, string base←配達日カレンダーで使いまわしたい
	 */
	public static function saveConfig(string $key="", $v="", string $base=""){
		if(!strlen($base)) $base = "calendar.config";
		SOYShop_DataSets::put($base . "." . $key, $v);
	}

	public static function getWeek(){
		$name = array("sun", "mon", "tue", "wed", "thu", "fri", "sat");
		$jp = array("日曜", "月曜", "火曜", "水曜", "木曜", "金曜", "土曜");
		$week = array();
		for($i = 0; $i < 7; $i++){
			$week[] = array("name" => $name[$i], "jp" => $jp[$i]);
		}
		
		return $week;	
	}

	public static function getDayOfWeekConfig(string $base=""){
		if(!strlen($base)) $base = "calendar.config";
		try{
			$dow = SOYShop_DataSets::get($base . ".day_of_week");
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
	public static function getMdConfig(bool $isText=false, string $base=""){
		return self::_commonConfig($base, "md", $isText);
	}
	
	/**
	 * 年月日での設定
	 */
	public static function getYmdConfig(bool $isText=false, string $base=""){
		return self::_commonConfig($base, "ymd", $isText);
	}
	
	/**
	 * 営業日
	 */
	public static function getBDConfig(bool $isText=false, string $base=""){
		return self::_commonConfig($base, "business_day", $isText);
	}

	/**
	 * 例外の配送日
	 */
	public static function getDDConfig(bool $isText=false, string $base=""){
		return self::_commonConfig($base, "delivery_day", $isText);
	}
	
	/**
	 * その他の日
	 */
	public static function getOtherConfig(bool $isText=false, $base=""){
		return self::_commonConfig($base, "other_day", $isText);
	}

	private static function _commonConfig(string $base="", string $key="", bool $isText=false){
		if(!strlen($base)) $base = "calendar.config";
		$cnf = SOYShop_DataSets::get($base . "." . $key, array());
		return ($isText) ? implode("\n", $cnf) : $cnf;
	}

	/**
	 * convert Ymd
	 * @param string
	 * @return string
	 */
	public static function ymd(string $date){
		$array = explode("\n", $date);

		$val = array();
		foreach($array as $line){
			$line = mb_convert_kana(trim($line), "a");
			if(preg_match("|^\d{4}\/\d{2}\/\d{2}$|", $line) || preg_match("|^\d{4}-\d{2}-\d{2}$|", $line)){
				$line = str_replace("-", "/", $line);
				$val[] = $line;
			}
		}
		return $val;
	}

	/**
	 * convert md
	 * @param string
	 * @return string
	 */
	public static function md(string $date){
		$arr = explode("\n", $date);

		$val = array();
		foreach($arr as $line){
			$line = mb_convert_kana(trim($line), "a");
			if(preg_match("|^\d{2}\/\d{2}$|", $line) || preg_match("|^\d{2}-\d{2}$|", $line)){
				$line = str_replace("-", "/", $line);
				$val[] = $line;
			}
		}
		return $val;
	}
}