<?php

class RemovePage extends WebPage{

	private $error=false;

	function doPost(){
		$start = soycalendar_split_array_by_text($_POST["start"]["start_calendar"]);
		$startDate = soycalendar_get_schedule_by_array($start);

		$end = soycalendar_split_array_by_text($_POST["end"]["end_calendar"]);
		$endDate = soycalendar_get_schedule_by_array($end);

		if(soy2_check_token() && $endDate >= $startDate){
			$titleId = (isset($_POST["titleId"]) && (int)$_POST["titleId"] > 0) ? (int)$_POST["titleId"] : 0;

			$cnt = date("n", $endDate) - date("n", $startDate) + 1;
			if(date("Y", $endDate) != date("Y", $startDate)) $cnt += 12;

			SOY2Logic::createInstance("logic.RemoveLogic")->removeSchedules($startDate, $endDate, $cnt, $titleId);

			CMSApplication::jump();
		}

		$this->error = true;
	}

    function __construct() {

    	parent::__construct();

		DisplayPlugin::toggle("error", $this->error);

    	$this->addForm("form");

    	$this->addInput("start_calendar", array(
    		"name" => "start[start_calendar]",
    		"value" => date("Y/m/d"),
    		"required" => true,
    		"readonly" => true
    	));

    	$this->addInput("end_calendar", array(
    		"name" => "end[end_calendar]",
    		"value" => date("Y/m/d"),
    		"required" => true,
    		"readonly" => true
    	));

    	$this->addSelect("title", array(
    		"name" => "titleId",
    		"options" => CalendarAppUtil::getTitleList(),
    	));
    }
}
