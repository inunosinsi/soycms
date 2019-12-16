<?php

/**
 * 各曜日
 */
class DayOfWeekHolidayListComponent extends HTMLList{

	private $config;
	
	function populateItem($entity, $key, $index){

		//第1から第5まで
		$this->createAdd("week_list","DayOfWeekListComponent", array(
			"list" => range(1,5),
			"dow" => $entity,
			"day" => $key,
			"config" => $this->config
		));

		//曜日
		$this->addLabel("day_label", array(
			"text" => (isset($entity["jp"])) ? $entity["jp"] : ""
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