<?php

/**
 * 第1から第5
 */
class DayOfWeekListComponent extends HTMLList{
	
	private $config;
	private $dow;
	private $day;
	
	
	function populateItem($entity, $key, $index){		
		//第1から第5まで
		$this->addCheckBox("day", array(
			"name" => "dow[$entity][" . @$this->day . "]",
			"elementId" => "dow_" . @$this->dow["name"] . "_" . $entity,
			"label" => "第" . $entity . @$this->dow["jp"],
			"selected" => (is_numeric($entity)) ? self::_check($entity) : false,
			"value" => 1
		));
	}

	private function _check(int $w){
		if(isset($this->config[$w]) && !is_null($this->config[$w])){
			if(in_array(@$this->day, $this->config[$w])){
				return true;
			}
		}
		return false;
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