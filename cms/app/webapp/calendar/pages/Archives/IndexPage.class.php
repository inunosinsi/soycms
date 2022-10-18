<?php

class IndexPage extends WebPage{

    function __construct() {

    	$year = (isset($_GET["year"]))?$_GET["year"]:date("Y");
    	$month = (isset($_GET["month"]))?$_GET["month"]:date("n");

    	parent::__construct();

    	$this->addLabel("archives", array(
    		"text" => $year."年".$month."月"
    	));

    	$this->addForm("form", array(
    		"method" => "get"
    	));

    	$this->addSelect("year", array(
    		"name" => "year",
    		"options" => range(2010,date("Y")+1),
    		"selected" => $year
    	));
    	$this->addSelect("month", array(
    		"name" => "month",
    		"options" => range(1,12),
    		"selected" => $month
    	));

    	if(strlen($month)==1)$month = "0".$month;

    	$this->addLabel("calendar", array(
    		"html" => SOY2Logic::createInstance("logic.CalendarLogic")->getCalendar($year,$month,true)
    	));
    }
}
