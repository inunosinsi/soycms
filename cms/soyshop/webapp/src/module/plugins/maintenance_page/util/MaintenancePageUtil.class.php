<?php

class MaintenancePageUtil {

	//メンテナンスページは既に作成されているか？
	public static function isMaintenancePage(){
		SOY2::import("domain.site.SOYShop_Page");
		return (is_numeric(soyshop_get_page_object_by_uri(SOYShop_Page::MAINTENANCE)->getId()));
	}

	/**
	 * メンテナンスページを有効にしているか？
	 * trueの場合はメンテナンスモード
	 * @return bool
	 */
	public static function checkActive(){
		$cnf = self::_config();

		//通常の表示設定がonであればtrueを返す
		if(isset($cnf["on"]) && is_numeric($cnf["on"]) && (int)$cnf["on"] === 1) return true;

		//時限設定自体があるか？確認
		if(!isset($cnf["timming"]) || !is_array($cnf["timming"])) return false;
		$timeCnf = $cnf["timming"];

		//時限設定がonになっていなければ、確認をやめる
		if(!isset($timeCnf["on"]) || !is_numeric($timeCnf["on"]) || (int)$timeCnf["on"] !== 1) return false;

		/** 時限設定した時刻が今よりも前の場合はメンテナンスモードをon **/
		$start = self::_getTimmingSettingTimestamp($timeCnf, "start");
		$end = self::_getTimmingSettingTimestamp($timeCnf, "end");
		$now = time();
		if($start <= $now && $now < $end) return true;

		return false;
	}

	public static function getConfig(){
		return self::_config();
	}

	/**
	 * @return array
	 */
	private static function _config(){
		return SOYShop_DataSets::get("maintenance_page.config", array(
			"on" => 0,
			"timming" => array(
				"on" => 0,
				"date" => array("start" => "", "end" => ""),
				"time" => array("start" => "", "end" => "")
			)
		));
	}

	public static function saveConfig(array $values){
		$values["on"] = (isset($values["on"]) && is_numeric($values["on"])) ? (int)$values["on"] : 0;
		$values["timming"]["on"] = (isset($values["timming"]["on"]) && is_numeric($values["timming"]["on"])) ? (int)$values["timming"]["on"] : 0;
		return SOYShop_DataSets::put("maintenance_page.config", $values);
	}

	/**
	 * 時限設定で設定した値のタイムスタンプを取得する
	 * @param array
	 + @return int
	 */
	private static function _getTimmingSettingTimestamp(array $timeCnf, string $mode="start"){
		$date = (isset($timeCnf["date"][$mode])) ? $timeCnf["date"][$mode] : null;
		if(is_null($date)){
			switch($mode){
				case "start":
					$date = date("Y-m-d", strtotime("-10 year"));
					break;
				case "end":
					$date = date("Y-m-d", strtotime("+10 year"));
					break;
			}
		}

		// YYYY/mm/ddの場合、YYYY-dd-mmに変更する
		if(is_numeric(strpos($date, "/"))) $date = str_replace("/", "-", $date); 
		if(!preg_match('/\d{4}-\d{2}-\d{2}/', $date)) {
			switch($mode){
				case "start":
					return strtotime("-10 year");
				case "end":
					return strtotime("+10 year");
			}
		}

		//timeの記述が正しいか？も最後に見る
		$time = (isset($timeCnf["time"][$mode])) ? $timeCnf["time"][$mode] : null;
		if(is_null($time)){
			switch($mode){
				case "start":
					$time = "00:00";
					break;
				case "end":
					$time = "23:59";
					break;
			}
		}
		$t = (isset($time) && strlen($time) && preg_match('/\d{2}:\d{2}/', $time)) ? $time.":00" : null;
		if(is_null($t)){
			switch($mode){
				case "start":
					$t = "00:00:00";
					break;
				case "end":
					$t = "23:59:59";
					break;
			}
		}
		return strtotime($date." ".$t);
	}
}
