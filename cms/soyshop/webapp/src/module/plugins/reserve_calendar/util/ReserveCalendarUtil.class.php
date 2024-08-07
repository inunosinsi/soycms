<?php

class ReserveCalendarUtil{

	const IS_TMP = 1;	//注文時の仮登録あり
	const NO_TMP = 0;	//注文時の仮登録なし

	const IS_SEND = 1;		//仮登録時にメール文面に本登録用のURLを含める
	const NO_SEND = 0;		//仮登録時にメール文面に本登録用のURLを含めない

	const IS_ONLY = 1;	//注文時の商品個数が1個のみに制限
	const NO_ONLY = 0;

	const IS_SHOW = 1;	//表示
	const NO_SHOW = 0;	//非表示

	const RESERVE_LIMIT = 0;
	const RESERVE_LIMIT_IGNORE = 1;	//管理画面で残席数以上の予約を行うことが出来る
	const RESERVE_DISPLAY_CANCEL_BUTTON = 1;	//管理画面の予約詳細でキャンセルボタンを表示する

/* sync customfield config */
	const DELIVERY_TWO_DAYS = "1～2営業日";
	const DELIVERY_FOUR_DAYS = "3～4営業日";
	const DELIVERY_ONE_WEEK = "1週間以降";
	const DELIVERY_TWO_WEEK = "2週間以降";
	const DELIVERY_THREE_WEEK = "3週間以降";
	const DELIVERY_ONE_MONTH = "1ヶ月以降";
	const DELIVERY_TWO_MONTH = "2ヶ月以降";
	const DELIVERY_BACK_ORDER = "お取り寄せ";

	const DISPLAY_PERIOD_CONFIG_FIELD_ID = "reserve_calendar_period";

	const PERIOD_MODE_START = 0;
	const PERIOD_MODE_END = 1;

	private $baseDate;

	public static function getCartAttributeId($optionId, $itemIndex, $itemId){
		return self::_getCartAttributeId($optionId, $itemIndex, $itemId);
	}

	private static function _getCartAttributeId($optionId, $itemIndex, $itemId){
		return "reserve_calendar_" . $optionId . "_" . $itemIndex . "_" . $itemId;
	}

	public static function getConfig(){
		$cnf = SOYShop_DataSets::get("reserve_calendar.config", array(
			"tmp" => self::NO_TMP,
			"send_at_time_tmp" => self::IS_SEND,
			"deadline" => 0,
			"only" => self::NO_ONLY,
			"show_price" => self::NO_SHOW,
			"ignore" => self::RESERVE_LIMIT,
			"cancel_button" => self::RESERVE_DISPLAY_CANCEL_BUTTON,
			"tmp_cancel_button" => self::NO_SHOW
		));

		if(!isset($cnf["send_at_time_tmp"])) $cnf["send_at_time_tmp"] = self::IS_SEND;

		return $cnf;
	}

	public static function saveConfig($values){
		$values["tmp"] = (isset($values["tmp"])) ? (int)$values["tmp"] : self::NO_TMP;
		$values["send_at_time_tmp"] = (isset($values["send_at_time_tmp"])) ? (int)$values["send_at_time_tmp"] : self::NO_SEND;
		$values["deadline"] = (isset($values["deadline"]) && is_numeric($values["deadline"])) ? (int)$values["deadline"] : 0;
		$values["only"] = (isset($values["only"])) ? (int)$values["only"] : self::NO_ONLY;
		$values["ignore"] = (isset($values["ignore"])) ? (int)$values["ignore"] : self::RESERVE_LIMIT;
		$values["cancel_button"] = (isset($values["cancel_button"])) ? (int)$values["cancel_button"] : self::RESERVE_DISPLAY_CANCEL_BUTTON;
		SOYShop_DataSets::put("reserve_calendar.config", $values);
	}

	public static function getAutoConfig(int $itemId){
		$v = SOYShop_DataSets::get("reserve_calendar.auto_" . $itemId, array(
			"register" => 0,
			"seat" => 0
		));

		return $v;
	}

	public static function saveAutoConfig(int $itemId, array $values){
		SOYShop_DataSets::put("reserve_calendar.auto_" . $itemId, $values);
	}

	public static function getWeekConfig(int $itemId){
		return SOYShop_DataSets::get("reserve_calendar.week_" . $itemId, array(0, 6));
	}
	public static function saveWeekConfig(int $itemId, array $values){
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

	public static function checkIsDayOfWeekConfig(int $itemId){
		return (!is_null(SOYShop_DataSets::get("reserve_calendar.day_of_week_" . $itemId, null)));
	}

	public static function getDayOfWeekConfig(int $itemId){
		$dow = SOYShop_DataSets::get("reserve_calendar.day_of_week_" . $itemId, array());
		if(!count($dow)){
			for($i = 1; $i < 6; $i++){
				$dow[$i] = array();
			}
		}
		return $dow;
	}

	public static function saveDayOfWeekConfig(int $itemId, array $values){
		SOYShop_DataSets::put("reserve_calendar.day_of_week_" . $itemId, $values);
	}

	/**
	 * 月日での設定
	 */
	public static function getMdConfig(int $itemId, bool $isText=false){
		$config = SOYShop_DataSets::get("reserve_calendar.md_" . $itemId, array());
		if($isText) $config = implode("\n", $config);

		return $config;
	}

	public static function saveMdConfig(int $itemId, array $values){
		SOYShop_DataSets::put("reserve_calendar.md_" . $itemId, $values);
	}

	/**
	 * 年月日での設定
	 */
	public static function getYmdConfig(int $itemId, bool $isText=false){
		$config = SOYShop_DataSets::get("reserve_calendar.ymd_" . $itemId, array());
		if($isText)$config = implode("\n", $config);
		return $config;
	}

	public static function saveYmdConfig(int $itemId, array $values){
		SOYShop_DataSets::put("reserve_calendar.ymd_" . $itemId, $values);
	}

	/**
	 * 営業日
	 */
	public static function getBDConfig(int $itemId, bool $isText=false){
		$config = SOYShop_DataSets::get("reserve_calendar.business_day_" . $itemId, array());
		if($isText)$config = implode("\n", $config);
		return $config;
	}

	public static function saveBDConfig(int $itemId, array $values){
		SOYShop_DataSets::put("reserve_calendar.business_day_" . $itemId, $values);
	}

	/**
	 * その他の日
	 */
	public static function getOtherConfig(int $itemId, bool $isText=false){
		$config = SOYShop_DataSets::get("reserve_calendar.other_day_" . $itemId, array());
		if($isText)$config = implode("\n", $config);

		return $config;
	}

	public static function saveOtherConfig(int $itemId, array $values){
		SOYShop_DataSets::put("reserve_calendar.other_day_" . $itemId, $values);
	}

	/**
	 * 他のプラグインで営業日のチェックができるようにするメソッド
	 * @param int timestamp, int
	 * @return bool 営業日であればtrue
	 */
	public static function isBD(int $timestamp, int $itemId){
		static $l;
		if(is_null($l)) $l = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Calendar.HolidayLogic", array("itemId" => $itemId));
		if($timestamp === 0) return false;
		return $l->isBD(soyshop_shape_timestamp($timestamp));
	}


	/** 文字列から時間帯を取得してカレンダーにスケジュールを表示するか決める **/
	public static function checkLabelString(string $label, int $y, int $m, int $d){
		$now = time();
		if(soyshop_convert_timestamp_on_array(array("year" => $y, "month" => $m, "day" => $d)) > $now) return true;	//明日以降は必ずtrue

		$label = trim($label);
		if($label == "午前") $label = "11:00";

		//半角に変換
		$old = array("０", "１", "２", "３", "４", "５", "６", "７", "８", "９", "：", "ー");
		$new = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9", ":", "-");
		for($i = 0; $i < count($old); $i++){
			$label = str_replace($old[$i], $new[$i], $label);
		}

		if(is_numeric(strpos($label, "時"))){
			preg_match('/\d{1,2}時$/', $label, $tmp);
			if(isset($tmp[0])){
				$label = str_replace("時", ":00", $label);
			}
		}

		//00:00の形式でなければtrueを返す
		preg_match('/\d{1,2}:\d{2}/', $label, $tmp);
		if(!isset($tmp[0])) return true;

		$v = explode(":", $label);
		//念の為
		if(!is_numeric($v[0]) || !is_numeric($v[1])) return true;

		return (mktime($v[0], $v[1], 0, $m, $d, $y) > $now);
	}

	/** 便利なメソッド **/
	public static function getScheduleByItemIndexAndItemId(CartLogic $cart, int $itemIndex, int $itemId){
		static $schedules, $scheduleLogic;
		if(is_null($schedules)) $schedules = array();
		if(is_null($scheduleLogic)) $scheduleLogic = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Schedule.ScheduleLogic");
		$key = self::_getCartAttributeId("schedule_id", $itemIndex, $itemId);
		$scheduleId = (int)$cart->getAttribute($key);
		if(isset($schedules[$scheduleId])) return $schedules[$scheduleId];

		$schedules[$scheduleId] = $scheduleLogic->getScheduleById($scheduleId);
		return $schedules[$scheduleId];
	}

	//定員数が0でないか？
	public static function checkIsUnsoldSeatByScheduleId(int $scheduleId){
		static $results;
		if(is_null($results)) $results = array();
		if($scheduleId < 1) return false;

		if(isset($results[$scheduleId])) return $results[$scheduleId];

		$results[$scheduleId] = self::_reserveLogic()->checkIsUnsoldSeatByScheduleId($scheduleId);
		return $results[$scheduleId];
	}

	//残席数を調べる @ToDo 仮登録を含めるか？
	public static function getCountUnsoldSeat(SOYShopReserveCalendar_Schedule $schedule){
		static $results;
		if(is_null($results)) $results = array();
		if(is_null($schedule->getId())) return false;

		if(isset($results[$schedule->getId()])) return $results[$schedule->getId()];

		return $schedule->getUnsoldSeat() - self::_reserveLogic()->getReservedCountByScheduleId($schedule->getId(), false, true);
	}

	//公開期限設定内であるか？
	public static function checkIsPublicationPeriod(int $itemId, int $year, int $month, int $mode=self::PERIOD_MODE_END){
		if(!soyshop_get_item_object($itemId)->isPublished()) return false;

		$mod = ($mode === self::PERIOD_MODE_START) ? "start" : "end";
		$v = soyshop_get_item_attribute_value($itemId, ReserveCalendarUtil::DISPLAY_PERIOD_CONFIG_FIELD_ID."_".$mod);
		if(!is_numeric($v)) return true;	// 設定がない場合は必ずtrue

		$y = (int)date("Y");
		$m = (int)date("n")+(int)$v;
		if($m > 12){
			$y++;
			$m -= 12;
		}
		if($y > $year) return false;	// 年の時点で超えている場合は必ずfalse
		if($m < $month) return false;	// 月の方で比較する
		return true;
	}

	private static function _reserveLogic(){
		static $logic;
		if(is_null($logic)) $logic = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Reserve.ReserveLogic");
		return $logic;
	}

	/** セッション **/
	public static function getSessionValue(string $key){
		return SOY2ActionSession::getUserSession()->getAttribute("reserve_calender_session_" . $key);
	}

	public static function saveSessionValue(string $key, $value){
		SOY2ActionSession::getUserSession()->setAttribute("reserve_calender_session_" . $key, $value);
	}
}
