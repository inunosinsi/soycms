<?php

class AutoDeleteOrderUtil {

	private static function _types(){
		return array(
			"invalid",		//無効注文
			"cancel",		//キャンセル注文
			"pre",			//仮登録注文
			"auto_cancel"	//自動キャンセル
		);
	}

	public static function getTypes(){
		return self::_types();
	}

	public static function getConfig(){
		$conf = SOYShop_DataSets::get("auto_delete_order.config", null);
		if(!is_null($conf)) return $conf;

		foreach(self::_types() as $t){
			$conf[$t] = 1;
			$conf[$t . "_timming"] = (strpos($t, "auto") === 0) ? 48 : 3;
		}

		return $conf;
	}

	public static function saveConfig($values){
		foreach(self::_types() as $t){
			if(!isset($values[$t]) || !is_numeric($values[$t])) $values[$t] = 0;
		}

		SOYShop_DataSets::put("auto_delete_order.config", $values);
	}

}
