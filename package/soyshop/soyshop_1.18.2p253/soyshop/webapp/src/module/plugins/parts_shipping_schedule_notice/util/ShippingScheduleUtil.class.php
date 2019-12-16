<?php

class ShippingScheduleUtil {

	const BIZ_AM = "biz_am";	//営業日の午前
	const BIZ_PM = "biz_pm";	//営業日の午後
	const HOL_AM = "hol_am";	//定休日の午前
	const HOL_PM = "hol_pm";	//定休日の午後

	//文言設定のパターン
	private static function _type(){
		return array(
			self::BIZ_AM => "営業日の午前",
			self::BIZ_PM => "営業日の午後",
			self::HOL_AM => "定休日の午前",
			self::HOL_PM => "定休日の午後"
		);
	}

	public static function getPatterns(){
		return array_keys(self::_type());
	}

	public static function getLabel($type){
		$types = self::_type();
		return (isset($types[$type])) ? $types[$type] : $types[0];
	}

	//使用できる置換文字列
	public static function getUsabledReplaceWords(){
		return array(
			"TODAY_Y" => "今日の日付の年",
			"TODAY_M" => "今日の日付の月",
			"TODAY_D" => "今日の日付の日",
			"TODAY_W" => "今日の日付の曜日",
			"SCH_Y" => "出荷予定日の日付の年",
			"SCH_M" => "出荷予定日の日付の月",
			"SCH_D" => "出荷予定日の日付の日",
			"SCG_W" => "出荷予定日の日付の曜日",
		);
	}

	//第二引数の$afterは何日後という意味
	public static function replace($str, $after){
		if(strpos($str, "##TODAY_Y##") !== false) $str = str_replace("##TODAY_Y##", date("Y"), $str);
		if(strpos($str, "##TODAY_M##") !== false) $str = str_replace("##TODAY_M##", date("n"), $str);
		if(strpos($str, "##TODAY_D##") !== false) $str = str_replace("##TODAY_D##", date("j"), $str);
		if(strpos($str, "##TODAY_W##") !== false) $str = str_replace("##TODAY_W##", self::_convertWeeks(date("w")), $str);

		$time = time() + $after * 24 * 60 * 60;
		if(strpos($str, "##SCH_Y##") !== false) $str = str_replace("##SCH_Y##", date("Y", $time), $str);
		if(strpos($str, "##SCH_M##") !== false) $str = str_replace("##SCH_M##", date("n", $time), $str);
		if(strpos($str, "##SCH_D##") !== false) $str = str_replace("##SCH_D##", date("j", $time), $str);
		if(strpos($str, "##SCH_W##") !== false) $str = str_replace("##SCH_W##", self::_convertWeeks(date("w", $time)), $str);
		return $str;
	}

	private static function _convertWeeks($w){
		$weeks = array("日", "月", "火", "水", "木", "金", "土");
		return (isset($weeks[$w])) ? $weeks[$w] : $weeks[0];
	}

	public static function getConfig(){
		return SOYShop_DataSets::get("parts_shipping_schedule_notice.config", array());
	}

	public static function save($values){
		SOYShop_DataSets::put("parts_shipping_schedule_notice.config", $values);
	}
}
