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

		$this->addModel("is_price", array(
			"visible" => ((int)$entity["price"] > 0)
		));

		$this->addLabel("price", array(
			"text" => number_format($entity["price"])
		));
	}

	function setSelected($selected){
		$this->selected = $selected;
	}
}
