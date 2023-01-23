<?php

class RemovePage extends WebPage{

	private $error=false;

	function doPost(){

		$startDate = soycalendar_get_schedule_by_array($_POST["start"]);
		$endDate = soycalendar_get_schedule_by_array($_POST["end"]);

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

		$yRange = range(CalendarAppUtil::getFirstItemScheduleDateYear(), date("Y")+1);
    	$this->addSelect("year", array(
    		"name" => "start[year]",
    		"options" => $yRange,
    		"selected" => date("Y")
    	));
    	$this->addSelect("month", array(
    		"name" => "start[month]",
    		"options" => range(1,12),
    		"selected" => date("n")
    	));
    	$this->addSelect("day", array(
    		"name" => "start[day]",
    		"options" => range(1,31),
    		"selected" => date("j")
    	));

    	$this->addSelect("end_year", array(
    		"name" => "end[year]",
    		"options" => $yRange,
    		"selected" => date("Y")
    	));
    	$this->addSelect("end_month", array(
    		"name" => "end[month]",
    		"options" => range(1,12),
    		"selected" => date("n")
    	));
    	$this->addSelect("end_day", array(
    		"name" => "end[day]",
    		"options" => range(1,31),
    		"selected" => date("j")
    	));

    	$this->addSelect("title", array(
    		"name" => "titleId",
    		"options" => CalendarAppUtil::getTitleList(),
    	));
    }
}
