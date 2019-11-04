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

	//任意の指定月のカレンダー
	for($i = 1; $i <= 10; $i++){
		$cal = "";
		if(strpos($html, "cms:id=\"specify_calender_" . $i . "\"")){
			preg_match('/cms:id="specify_calender_' . $i . '?".*?cms:specify="(.*?)"/', $html, $tmp);
			if(isset($tmp[1]) && is_numeric($tmp[1])){
				$cal = $logic->getSpecifyCalendar(false, true, (int)$tmp[1]);
			}
		}

		$obj->addLabel("specify_calender_" . $i, array(
			"soy2prefix" => $prefix,
			"html" => $cal
		));
	}

	SOYAppUtil::resetAppMode($old);

	$obj->display();
}
