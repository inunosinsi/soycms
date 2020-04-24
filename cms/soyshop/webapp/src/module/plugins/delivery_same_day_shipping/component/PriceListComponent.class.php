<?php

class PriceListComponent extends HTMLList{

	var $prices;

	function populateItem($entity, $key, $counter, $length){
		$this->addModel("second_table", array(
			"visible" => ($counter == 24),
		));
		$this->addCheckBox("area_check", array(
			"label"    => $entity,
			"elementId"  => "price_check_" . $key,
			"targetId" => "price_input_" . $key,
		));
		$this->addInput("price", array(
			"attr:id"  => "price_input_" . $key,
			"value" => (isset($this->prices[$key])) ? $this->prices[$key] : "",
			"name"  => "price[$key]"
		));
	}

	function setPrices($prices) {
		$this->prices = $prices;
	}
}
