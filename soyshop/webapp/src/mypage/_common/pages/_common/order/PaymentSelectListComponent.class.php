<?php

class PaymentSelectListComponent extends HTMLList {

	private $selected;

	protected function populateItem($entity, $key) {

		$this->addLabel("name", array(
			"text" => (isset($entity["name"])) ? $entity["name"] : ""
		));

		$this->addCheckBox("label", array(
			"name" => "Payment",
			"value" => $key,
			"selected" => ($key == $this->selected),
			"label" => (isset($entity["name"])) ? $entity["name"] : ""
		));

		$this->addLabel("description", array(
			"html" => (isset($entity["description"])) ? $entity["description"] : ""
		));

		$price = (isset($entity["price"]) && is_numeric($entity["price"])) ? (int)$entity["price"] : 0;
		$this->addModel("is_price", array(
			"visible" => ($price > 0)
		));

		$this->addLabel("price", array(
			"text" => number_format($price)
		));
	}

	function setSelected($selected){
		$this->selected = $selected;
	}
}
