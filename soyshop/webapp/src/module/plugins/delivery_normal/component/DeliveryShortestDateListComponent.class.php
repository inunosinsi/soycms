<?php

class DeliveryShortestDateListComponent extends HTMLList {

	private $mode=1;	// 1 or 2
	private $selected=array();

	function populateItem($entity, $key){
		$area = (is_string($entity)) ? $entity : "";
		
		$this->addCheckBox("area", array(
			"name" => "Date[delivery_shortest_date_after][".(string)$this->mode."][]",
			"value" => $key,
			"selected" => (is_numeric($key) && is_array($this->selected) && count($this->selected) && is_numeric(array_search($key, $this->selected))),
			"label" => $area
		));
	}

	function setMode($mode){
		$this->mode = $mode;
	}

	function setSelected($selected){
		$this->selected = $selected;
	}
}