<?php

/**
 * 第1から第5
 */
class DayOfWeekListComponent extends HTMLList{

	private $config;
	private $dow;
	private $day;


	function populateItem($entity, $key, $index){
		$entity = (is_numeric($entity)) ? (int)$entity : 0;

		$selected = false;
		if(isset($this->config[$entity]) && !is_null($this->config[$entity])){
			if(in_array(@$this->day, @$this->config[$entity])){
				$selected = true;
			}
		}

		//第1から第5まで
		$this->addCheckBox("day", array(
			"name" => "dow[$entity][" . @$this->day . "]",
			"elementId" => "dow_" . @$this->dow["name"] . "_" . $entity,
			"label" => "第" . $entity . @$this->dow["jp"],
			"selected" => $selected,
			"value" => 1
		));
	}

	function getConfig() {
		return $this->config;
	}
	function setConfig($config) {
		$this->config = $config;
	}

	function getDow() {
		return $this->dow;
	}
	function setDow($dow) {
		$this->dow = $dow;
	}

	function getDay() {
		return $this->day;
	}
	function setDay($day) {
		$this->day = $day;
	}
}
