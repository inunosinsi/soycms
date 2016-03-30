<?php

class WeekHolidayListComponent extends HTMLList{
	
	private $config;
	
	function populateItem($entity, $key, $index){

		$this->addCheckBox("dow", array(
			"name" => "week[$key]",
			"elementId" => "week_" . $entity["name"],
			"label" => $entity["jp"],
			"selected" => (isset($this->config) && in_array($key,$this->config)),
			"value" => 1
		));		
	}

	function getConfig() {
		return $this->config;
	}
	function setConfig($config) {
		$this->config = $config;
	}
}
?>