<?php
function soyshop_simple_calendar($html, $page){
		
	$obj = $page->create("soyshop_simple_calendar", "HTMLTemplatePage", array(
		"arguments" => array("soyshop_simple_calendar", $html)
	));
	
	$year = (isset($_GET["y"]) && is_numeric($_GET["y"])) ? (int)$_GET["y"] : (int)date("Y");
	$month = (isset($_GET["m"]) && is_numeric($_GET["m"])) ? (int)$_GET["m"] : (int)date("n");
	
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
	$itemIdList = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Calendar.LabelLogic")->registerdItemIdsOnLabel();
	
	foreach($itemIdList as $itemId){

		$obj->addLabel("calendar_" . $itemId, array(
			"soy2prefix" => "block",
			"html" => SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.View.CalendarLogic", array("itemId" => $itemId))->build($year, $month)
		));
	}
	
	if(count($itemIdList)){
		$obj->display();
	}else{
		ob_start();
		$obj->display();
		ob_end_clean();
	}
}
?>