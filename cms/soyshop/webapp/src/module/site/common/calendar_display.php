<?php
SOY2::import("util.SOYShopPluginUtil");
function soyshop_calendar_display(string $html, HTMLPage $page){

	$obj = $page->create("calendar_display", "HTMLTemplatePage", array(
		"arguments" => array("calendar_display", $html)
	));

	if(SOYShopPluginUtil::checkIsActive("parts_calendar")){
		$logic = SOY2Logic::createInstance("module.plugins.parts_calendar.logic.DisplayLogic");
		$currentCalendar = $logic->getCurrentCalendar();
		$nextCalendar = $logic->getNextCalendar();
	}else{
		$currentCalendar = "";
		$nextCalendar = "";
	}

	$obj->addLabel("current_calendar", array(
		"soy2prefix" => SOYSHOP_SITE_PREFIX,
		"html" => $currentCalendar
	));

	$obj->addLabel("next_calendar", array(
		"soy2prefix" => SOYSHOP_SITE_PREFIX,
		"html" => $nextCalendar
	));

	$obj->display();
}
