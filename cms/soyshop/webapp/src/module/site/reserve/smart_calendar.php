<?php
function soyshop_smart_calendar(string $html, HTMLPage $page){

	$obj = $page->create("soyshop_smart_calendar", "HTMLTemplatePage", array(
		"arguments" => array("soyshop_smart_calendar", $html)
	));

	SOY2::import("module.plugins.reserve_calendar.util.ReserveCalendarUtil");

	$year = (isset($_GET["y"]) && is_numeric($_GET["y"])) ? (int)$_GET["y"] : (int)date("Y");
	$month = (isset($_GET["m"]) && is_numeric($_GET["m"])) ? (int)$_GET["m"] : (int)date("n");
	
	//直近の空き予約を調べる → スマホ版の表示に影響を与えてしまうため廃止
	if(!isset($_GET["y"]) || !isset($_GET["m"])){
		//list($year, $month) = SOY2Logic::createInstance("module.plugins.calendar_expand_smart.logic.Schedule.SmartScheduleLogic")->findLatestScheduleDate($year, $month);
	}

/**
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
**/

	$reserved = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Reserve.ReserveLogic")->getReservedSchedulesByPeriod($year, $month);

	//昨月
	$prevY = $year;
	$prevM = $month - 1;
	if($prevM < 1){
		$prevY -= 1;
		$prevM = 12;
	}

	//次の月も調べる
	$nextM = $month + 1;
	$nextY = $year;
	if($nextM > 12){
		$nextM = 1;
		$nextY += 1;
	}
	$nextReserved = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Reserve.ReserveLogic")->getReservedSchedulesByPeriod($nextY, $nextM);
	if(count($nextReserved)) $reserved += $nextReserved;
	$GLOBALS["reserved_schedules"] = $reserved;

	if(!defined("RESERVE_CALENDAR_MODE")) define("RESERVE_CALENDAR_MODE", "bootstrap");

	$itemId = 0;
	$sess = SOY2ActionSession::getUserSession();
	if((isset($_GET["calendar_id"]) && is_numeric($_GET["calendar_id"])) || (isset($_GET["item_id"]) && is_numeric($_GET["item_id"]))){
		$itemId = (isset($_GET["calendar_id"]) && is_numeric($_GET["calendar_id"])) ? (int)$_GET["calendar_id"] : (int)$_GET["itemId"];
		$sess->setAttribute("smart_calendar_item_id", $itemId);
	}else{
		$itemId = $sess->getAttribute("smart_calendar_item_id");
		$itemId = (is_numeric($itemId)) ? (int)$itemId : 0;
	}

	//メモリの節約のために個別に出力できるようにする
	if($itemId > 0){
		$itemIdList = array($itemId);

		$obj->addLabel("item_name", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => soyshop_get_item_object($itemId)->getOpenItemName()
		));

		//ボタンの非同期設定 syncがtrueで同期 falseで非同期
		$sync = true;
		if(preg_match('/block:id=\"calendar\".*cms:async=\"(.*?)\"/', $html, $tmp)){
			if(isset($tmp[1]) && is_numeric($tmp[1]) && (int)$tmp[1] === 1) $sync = false;
		}

		$obj->addLabel("calendar", array(
			"soy2prefix" => "block",
			"html" => (soyshop_get_item_object($itemId)->isPublished()) ? SOY2Logic::createInstance("module.plugins.calendar_expand_smart.logic.View.CalendarLogic", array("itemId" => $itemId, "sync" => $sync))->build($year, $month) : ""
		));
	}else{
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
		
		if(count($itemIds)){
			foreach($itemIds as $itemId){
				//ボタンの非同期設定 syncがtrueで同期 falseで非同期
				$sync = true;
				if(preg_match('/block:id=\"calendar_' . $itemId . '\".*cms:async=\"(.*?)\"/', $html, $tmp)){
					if(isset($tmp[1]) && is_numeric($tmp[1]) && (int)$tmp[1] === 1) $sync = false;
				}

				$isPublished = ReserveCalendarUtil::checkIsPublicationPeriod($itemId, $year, $month);
				
				$obj->addLabel("calendar_" . $itemId, array(
					"soy2prefix" => "block",
					"html" => SOY2Logic::createInstance("module.plugins.calendar_expand_smart.logic.View.CalendarLogic", array("itemId" => $itemId, "sync" => $sync, "isPublished" => $isPublished))->build($year, $month)
				));
			}
		}
		$itemId = (count($itemIds)) ? $itemIds[0] : 0;
	}

	$url = soyshop_get_page_url($page->getPageObject()->getUri());

	//リンク
	$obj->addModel("prev_month", array(
		"soy2prefix" => SOYSHOP_SITE_PREFIX,
		"visible" => (mktime(0, 0, 0, $prevM + 1, 1, $prevY) - 1 > time())
	));

	$obj->addLink("prev_month_link", array(
		"soy2prefix" => SOYSHOP_SITE_PREFIX,
		"link" => $url . "?y=" . $prevY . "&m=" . $prevM
	));

	$obj->addModel("next_month", array(
		"soy2prefix" => SOYSHOP_SITE_PREFIX,
		"visible" => (ReserveCalendarUtil::checkIsPublicationPeriod($itemId, $year, $month+1))	// 翌月の公開設定を調べる
	));

	$obj->addLink("next_month_link", array(
		"soy2prefix" => SOYSHOP_SITE_PREFIX,
		"link" => $url . "?y=" . $nextY . "&m=" . $nextM
	));

	$obj->addLabel("caption", array(
		"soy2prefix" => SOYSHOP_SITE_PREFIX,
		"text" => $year . "年" . $month . "月"
	));



	//非同期の場合のjsファイルの挿入
	// $obj->addLabel("async_js", array(
	// 	"soy2prefix" => SOYSHOP_SITE_PREFIX,
	// 	"html" => "\n" . file_get_contents(SOY2::RootDir() . "module/plugins/reserve_calendar/js/async.js")
	// ));

	// $obj->addInput("async_cart_url", array(
	// 	"soy2prefix" => SOYSHOP_SITE_PREFIX,
	// 	"type" => "hidden",
	// 	"value" => soyshop_get_cart_url(true),
	// 	"attr:id" => "reserve_calendar_cart_url"
	// ));

	if(count($itemIds)){
		$obj->display();
	}else{
		ob_start();
		$obj->display();
		ob_end_clean();
	}
}
