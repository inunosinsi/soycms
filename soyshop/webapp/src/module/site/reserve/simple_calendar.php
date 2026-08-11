<?php
function soyshop_simple_calendar(string $html, HTMLPage $page){

	$obj = $page->create("soyshop_simple_calendar", "HTMLTemplatePage", array(
		"arguments" => array("soyshop_simple_calendar", $html)
	));

	SOY2::import("module.plugins.reserve_calendar.util.ReserveCalendarUtil");

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

	//先にblock:id="calendar_{itemId}"の記述を調べる
	$itemIds = array();
	$lines = explode("\n", $html);
	if(count($lines)){
		foreach($lines as $line){
			$line = trim($line);
			if(!strlen($line) || soy2_strpos($line, "block:id") < 0) continue;
			preg_match('/block:id=\"calendar_(\d*?)\"/', $line, $tmp);
			if(!isset($tmp[1]) || !is_numeric($tmp[1])) continue;
			$id = (int)$tmp[1];
			if(is_bool(array_search($id, $itemIds))) $itemIds[] = $id;
		}
	}
	unset($lines);
	
	//商品分だけタグを生成する
	if(!count($itemIds)) $itemIds = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Calendar.LabelLogic")->getRegisteredItemIdsOnLabel();

	foreach($itemIds as $itemId){

		//ボタンの非同期設定 syncがtrueで同期 falseで非同期
		$sync = true;
		if(preg_match('/block:id=\"calendar_' . $itemId . '\".*cms:async=\"(.*?)\"/', $html, $tmp)){
			if(isset($tmp[1]) && is_numeric($tmp[1]) && (int)$tmp[1] === 1) $sync = false;
		}

		$isPublished = ReserveCalendarUtil::checkIsPublicationPeriod($itemId, $year, $month);

		$obj->addLabel("calendar_" . $itemId, array(
			"soy2prefix" => "block",
			"html" => SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.View.CalendarLogic", array("itemId" => $itemId, "sync" => $sync, "isPublished" => $isPublished))->build($year, $month)
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

	if(count($itemIds)){
		$obj->display();
	}else{
		ob_start();
		$obj->display();
		ob_end_clean();
	}
}
