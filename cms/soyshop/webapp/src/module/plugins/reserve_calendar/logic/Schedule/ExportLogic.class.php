<?php

class ExportLogic extends SOY2LogicBase {

	function export(){
		$charset = "Shift-JIS";

		$labels = array("日時(2019-01-01のように指定)", "ラベルID", "残席数", "価格");

		//プラグインで項目を更に増やせるようにしたい
		SOYShopPlugin::load("soyshop.add.price.on.calendar");
 		$items = SOYShopPlugin::invoke("soyshop.add.price.on.calendar", array(
 			"mode" => "csv",
 		))->getList();

		if(count($items)){
			foreach($items as $moduleId => $v){
				$labels[$v["key"]] = $v["label"];
			}
		}

		header("Cache-Control: no-cache");
		header("Pragma: no-cache");
		header("Content-Disposition: attachment; filename=reserve_calendar_format" . date("Ymd") . ".csv");
		header("Content-Type: text/csv; charset=" . htmlspecialchars($charset) . ";");

		echo mb_convert_encoding(implode(",", $labels), $charset, "UTF-8");

	}
}
