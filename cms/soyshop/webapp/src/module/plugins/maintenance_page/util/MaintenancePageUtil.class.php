<?php

class MaintenancePageUtil {

	//メンテナンスページは既に作成されているか？
	public static function isMaintenancePage(){
		SOY2::import("domain.site.SOYShop_Page");
		return (is_numeric(soyshop_get_page_object_by_uri(SOYShop_Page::MAINTENANCE)->getId()));
	}

	//メンテナンスページを有効にしているか？
	public static function checkActive(){
		$cnf = self::_config();

		//通常の表示設定がonであればtrueを返す
		if(isset($cnf["on"]) && is_numeric($cnf["on"]) && (int)$cnf["on"] === 1) return true;

		//時限設定自体があるか？確認
		if(!isset($cnf["timming"]) || !is_array($cnf["timming"])) return false;
		$timeCnf = $cnf["timming"];

		//時限設定がonになっていなければ、確認をやめる
		if(!isset($timeCnf["on"]) || !is_numeric($timeCnf["on"]) || (int)$timeCnf["on"] !== 1) return false;

		//時限設定した時刻が今よりも前の場合はメンテナンスモードをon
		if(self::_getTimmingSettingTimestamp($timeCnf) > time()) return true;

		return false;
	}

	public static function getConfig(){
		return self::_config();
	}

	private static function _config(){
		return SOYShop_DataSets::get("maintenance_page.config", array(
			"on" => 0,
			"timming" => array(
				"on" => 0,
				"date" => "",
				"time" => ""
			)
		));
	}

	public static function saveConfig($values){
		$values["on"] = (isset($values["on"]) && is_numeric($values["on"])) ? (int)$values["on"] : 0;
		$values["timming"]["on"] = (isset($values["timming"]["on"]) && is_numeric($values["timming"]["on"])) ? (int)$values["timming"]["on"] : 0;
		return SOYShop_DataSets::put("maintenance_page.config", $values);
	}

	//時限設定で設定した値のタイムスタンプを取得する
	private static function _getTimmingSettingTimestamp($timeCnf){
		//dateの記述が正しいか？も最後に見る
		if(!isset($timeCnf["date"]) || !strlen($timeCnf["date"])) return strtotime("+1 day");

		// YYYY/mm/ddの場合、YYYY-dd-mmに変更する
		if(is_numeric(strpos($timeCnf["date"], "/"))) $timeCnf["date"] = str_replace("/", "-", $timeCnf["date"]); 
		if(!preg_match('/\d{4}-\d{2}-\d{2}/', $timeCnf["date"])) return strtotime("+1 day");

		//timeの記述が正しいか？も最後に見る
		$t = (isset($timeCnf["time"]) && strlen($timeCnf["time"]) && preg_match('/\d{2}:\d{2}/', $timeCnf["time"])) ? $timeCnf["time"] . ":00" : "00:00:00";
		return strtotime($timeCnf["date"] . " " . $t);
	}
}
