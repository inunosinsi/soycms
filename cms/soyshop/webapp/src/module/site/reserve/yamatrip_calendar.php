<?php
function soyshop_yamatrip_calendar($html, $page){

	$obj = $page->create("soyshop_yamatrip_calendar", "HTMLTemplatePage", array(
		"arguments" => array("soyshop_yamatrip_calendar", $html)
	));

	$year = (isset($_GET["y"]) && is_numeric($_GET["y"])) ? (int)$_GET["y"] : (int)date("Y");
	$month = (isset($_GET["m"]) && is_numeric($_GET["m"])) ? (int)$_GET["m"] : (int)date("n");

	//直近の空き予約を調べる
	if(!isset($_GET["y"]) || !isset($_GET["m"])){
		list($year, $month) = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Schedule.ScheduleLogic")->findLatestScheduleDate($year, $month);
	}

	if(get_class($page) == "detail_page"){	//itemIdはpageオブジェクトから取得
		$itemId = $page->getItem()->getId();
	}else if(isset($_GET["item_id"])){	//itemIdはGETで指定
		$itemId = (int)$_GET["item_id"];
	}else{
		$itemIdList = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Calendar.LabelLogic")->getRegisteredItemIdsOnLabel();
		if(count($itemIdList)){
			$itemId = (int)array_shift($itemIdList);
		}
	}

	//@ToDo 座席数等
	$GLOBALS["schedules"] = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Schedule.ScheduleLogic")->getScheduleList($itemId, $year, $month);

	//予約数
	$GLOBALS["reserved_schedules"] = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Reserve.ReserveLogic")->getReservedSchedulesByPeriod($year, $month);

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

	$url = soyshop_get_page_url($page->getPageObject()->getUri());
	if(get_class($page) == "detail_page"){
		$url .= "/" . $page->getItem()->getAlias();
	}

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
		"visible" => true	//常にtrue
	));

	$obj->addLink("next_month_link", array(
		"soy2prefix" => SOYSHOP_SITE_PREFIX,
		"link" => $url . "?y=" . $nextY . "&m=" . $nextM
	));

	$obj->addLabel("caption", array(
		"soy2prefix" => SOYSHOP_SITE_PREFIX,
		"text" => $year . "年" . $month . "月"
	));

	//商品分だけタグを生成する

	//残席数をJavaScriptのマップで出力
	$seats = array();
	foreach($GLOBALS["schedules"] as $d => $sch){
		foreach($sch as $schId => $v){
			$res = (isset($GLOBALS["reserved_schedules"][$schId])) ? (int)$GLOBALS["reserved_schedules"][$schId] : 0;
			$seats[$schId] = ($v["seat"] - $res);
		}
	}

	$js = "<script>var unsold_seat_list = {";
	foreach($seats as $schId => $unsold){
		$js .= $schId . ":" . $unsold . ",";
	}
	$js .= "};</script>";

	if(!defined("RESERVE_CALENDAR_MODE")) define("RESERVE_CALENDAR_MODE", "bootstrap");
	$obj->addLabel("calendar", array(
		"soy2prefix" => "block",
		"html" => SOY2Logic::createInstance("module.plugins.calendar_expand_yamatrip.logic.View.CalendarLogic", array("itemId" => $itemId))->build($year, $month) . "\n" . $js
	));

	$obj->addForm("form", array(
		"soy2prefix" => SOYSHOP_SITE_PREFIX,
		"action" => soyshop_get_cart_url(true)
	));

	$obj->addInput("adult_seat", array(
		"soy2prefix" => SOYSHOP_SITE_PREFIX,
		"name" => "Option[adult]",
		"value" => 1,
		"attr:max" => 10,
	));

	$obj->addInput("child_seat", array(
		"soy2prefix" => SOYSHOP_SITE_PREFIX,
		"name" => "Option[child]",
		"value" => 0,
		"attr:max" => 10,
	));


	$obj->addInput("seat", array(
		"soy2prefix" => SOYSHOP_SITE_PREFIX,
		"name" => "count",
		"value" => 0,
		"attr:max" => 1,
		"style" => "ime-mode:inactive;"
	));

	$obj->display();
}
