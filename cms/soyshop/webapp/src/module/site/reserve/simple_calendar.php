<?php
function soyshop_simple_calendar($html, $page){

	$obj = $page->create("soyshop_simple_calendar", "HTMLTemplatePage", array(
		"arguments" => array("soyshop_simple_calendar", $html)
	));

	$year = (isset($_GET["y"]) && is_numeric($_GET["y"])) ? (int)$_GET["y"] : (int)date("Y");
	$month = (isset($_GET["m"]) && is_numeric($_GET["m"])) ? (int)$_GET["m"] : (int)date("n");

	//直近の空き予約を調べる
	if(!isset($_GET["y"]) || !isset($_GET["m"])){
		list($year, $month) = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Schedule.ScheduleLogic")->findLatestScheduleDate($year, $month);
	}

	$obj->addForm("schedule_calendar_form", array(
		"soy2prefix" => SOYSHOP_SITE_PREFIX,
		"method" => "GET"
	));

	$obj->addSelect("schedule_year_select", array(
		"soy2prefix" => SOYSHOP_SITE_PREFIX,
		"name" => "y",
		"options" => range($year, $year + 1),
		"selected" => $year
	));

	$obj->addSelect("schedule_month_select", array(
		"soy2prefix" => SOYSHOP_SITE_PREFIX,
		"name" => "m",
		"options" => range(1, 12),
		"selected" => $month
	));

	$GLOBALS["reserved_schedules"] = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Reserve.ReserveLogic")->getReservedSchedulesByPeriod($year, $month);

	//商品分だけタグを生成する
	$itemIdList = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Calendar.LabelLogic")->getRegisteredItemIdsOnLabel();
	foreach($itemIdList as $itemId){

		//ボタンの非同期設定 syncがtrueで同期 falseで非同期
		$sync = true;
		if(preg_match('/block:id=\"calendar_' . $itemId . '\".*cms:async=\"(.*?)\"/', $html, $tmp)){
			if(isset($tmp[1]) && is_numeric($tmp[1]) && (int)$tmp[1] === 1) $sync = false;
		}
		$obj->addLabel("calendar_" . $itemId, array(
			"soy2prefix" => "block",
			"html" => (soyshop_get_item_object($itemId)->isPublished()) ? SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.View.CalendarLogic", array("itemId" => $itemId, "sync" => $sync))->build($year, $month) : ""
		));
	}

	//非同期の場合のjsファイルの挿入
	$obj->addLabel("async_js", array(
		"soy2prefix" => SOYSHOP_SITE_PREFIX,
		"html" => "\n" . file_get_contents(SOY2::RootDir() . "module/plugins/reserve_calendar/js/async.js")
	));

	$obj->addInput("async_cart_url", array(
		"soy2prefix" => SOYSHOP_SITE_PREFIX,
		"type" => "hidden",
		"value" => soyshop_get_cart_url(true),
		"attr:id" => "reserve_calendar_cart_url"
	));

	if(count($itemIdList)){
		$obj->display();
	}else{
		ob_start();
		$obj->display();
		ob_end_clean();
	}
}
