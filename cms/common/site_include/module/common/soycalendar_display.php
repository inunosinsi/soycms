<?php
function soycms_soycalendar_display($html, $page){

	$obj = $page->create("soycalendar_display", "HTMLTemplatePage", array(
		"arguments" => array("soycalendar_display", $html)
	));

	SOY2::import("util.SOYAppUtil");
	$old = SOYAppUtil::switchAppMode("calendar");

	$logic = SOY2Logic::createInstance("logic.CalendarLogic");
	$prefix = "cms";

	$obj->addLabel("prev_calendar" ,array(
		"soy2prefix" => $prefix,
		"html" => $logic->getPrevCalendar(false, false)
	));

	$obj->addLabel("current_calendar", array(
		"soy2prefix" => $prefix,
		"html" => $logic->getCurrentCalendar(false, true)
	));

	$obj->addLabel("next_calendar", array(
		"soy2prefix" => $prefix,
		"html" => $logic->getNextCalendar(false, true)
	));

	SOYAppUtil::resetAppMode($old);

	$obj->display();
}
