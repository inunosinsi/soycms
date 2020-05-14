<?php

class ShippingScheduleUtil {

	const BIZ_AM = "biz_am";	//営業日の午前
	const BIZ_PM = "biz_pm";	//営業日の午後
	const HOL_AM = "hol_am";	//定休日の午前
	const HOL_PM = "hol_pm";	//定休日の午後
	const HOL_CO = "hol_co";	//連休	英語でConsecutive holidaysと書く

	//文言設定のパターン
	private static function _type(){
		return array(
			self::BIZ_AM => "営業日の午前",
			self::BIZ_PM => "営業日の午後",
			self::HOL_AM => "定休日の午前",
			self::HOL_PM => "定休日の午後",
			self::HOL_CO => "連休"
		);
	}

	public static function getPatterns(){
		return array_keys(self::_type());
	}

	public static function getLabel($type){
		$types = self::_type();
		return (isset($types[$type])) ? $types[$type] : $types[0];
	}

	public static function buildUsabledReplaceWordsList(){
		$html = array();
		$html[] = "<table class=\"table table-striped\">";
		$html[] = "<caption>使用できる置換文字列</caption>";
		$html[] = "<thead><tr><th>置換文字列</th><th>種類</th></tr></thead>";
		$html[] = "<tbody>";
		foreach(self::_getUsabledReplaceWords() as $k => $w){
			$html[] = "<tr>";
			$html[] = "<td>##" . $k . "##</td>";
			$html[] = "<td>" . $w . "</td>";
			$html[] = "</tr>";
		}
		$html[] = "</tbody>";
		$html[] = "</table>";
		return implode("\n", $html);
	}

	public static function getUsabledReplaceWords(){
		return self::_getUsabledReplaceWords();
	}

	//使用できる置換文字列
	private static function _getUsabledReplaceWords(){
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


	public static function checkDuringConsecutiveHolidays($cnf){
		if(!isset($cnf["consecutive"][self::HOL_CO])) return false;
		list($start, $end) = self::_dividePeriod($cnf["consecutive"][self::HOL_CO]);
		if(is_null($start)) return false;
		if(!strpos($start, "/") || !strpos($end, "/")) return false;

		$periods = self::_generatePeriodArray($start, $end);
		if(!count($periods)) return false;
		
		$todayM = date("n");
		$todayD = date("j");

		foreach($periods as $p){
			if($p == $todayM . "/" . $todayD) return true;
		}

		return false;
	}

	private static function _dividePeriod($p){
		if(!strpos($p, "〜")) return array(null, null);
		$v = explode("〜", $p);
		return array(trim($v[0]), trim($v[1]));
	}

	private static function _generatePeriodArray($start, $end){
		$startV = explode("/", $start);
		$startM = (int)$startV[0];
		$startD = (int)$startV[1];

		$endV = explode("/", $end);
		$endM = (int)$endV[0];
		$endD = (int)$endV[1];

		$list = array();
		if($startM === $endM){
			if($startD > $endD) return array();	//開始日よりも終了日の方が前の場合はエラー
			for($i = $startD; $i <= $endD; $i++){
				$list[] = $startM . "/" . $i;
			}
		}else{	//月をまたぐ場合	2ヶ月以上の連休は設定できない
			for($i = $startD; $i <= 31; $i++){
				$list[] = $startM . "/" . $i;
			}
			for($i = 1; $i <= $endD; $i++){
				$list[] = $endM . "/" . $i;
			}

		}

		return $list;
	}

	public static function getConfig(){
		return SOYShop_DataSets::get("parts_shipping_schedule_notice.config", array());
	}

	public static function save($values){
		SOYShop_DataSets::put("parts_shipping_schedule_notice.config", $values);
	}
}
