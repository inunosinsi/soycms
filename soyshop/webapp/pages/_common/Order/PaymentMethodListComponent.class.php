<?php

class PaymentMethodListComponent extends HTMLList {

	private $selected;

	protected function populateItem($entity, $key){

		$this->addCheckBox("payment_method", array(
			"name" => "Payment",
			"value" => $key,
			"selected" => ($key == $this->selected),
			"label" => (isset($entity["name"])) ? $entity["name"] : ""
		));
	}

	function setSelected($selected){
		$this->selected = $selected;
	}
}
