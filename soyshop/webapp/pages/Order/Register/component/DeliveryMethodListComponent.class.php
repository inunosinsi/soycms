<?php

class DeliveryMethodListComponent extends HTMLList{

	private $selected;

	protected function populateItem($entity, $key, $counter, $length){
		$this->addCheckBox("delivery_method", array(
			"name" => "delivery_module",
			"value" => $key,
			"selected" => ( ($this->selected == $key) || ($length == 1) ),
			"label" => (isset($entity["name"])) ? $entity["name"] : ""
		));

		$this->addLabel("delivery_name", array(
			"text" => (isset($entity["name"])) ? $entity["name"] : ""
		));

		$this->addLabel("delivery_description", array(
			"html" => (isset($entity["description"])) ? $entity["description"] : ""
		));

		$this->addLabel("delivery_charge", array(
			"text" => (isset($entity["price"])) ? soy2_number_format($entity["price"])." 円" : "",
		));
	}

	function setSelected($selected) {
		$this->selected = $selected;
	}
}
