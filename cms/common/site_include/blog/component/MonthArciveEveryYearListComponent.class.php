<?php

class MonthArciveEveryYearListComponent extends HTMLList{

	var $monthPageUri;
	var $format;

	function setMonthPageUri($uri){
		$this->monthPageUri = $uri;
	}

	function setFormat($format){
		$this->format = $format;
	}

	protected function populateItem($month_list, $year){

		$this->addLabel("year", array(
			"text" => $year,
			"soy2prefix" => "cms"
		));

		$this->createAdd("archive","MonthArciveListComponent",array(
			"list" => $month_list,
			"monthPageUri" => $this->monthPageUri,
			"secretMode" => false,
			"soy2prefix" => "cms"
		));
	}
}
