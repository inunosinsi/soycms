<?php

class PriceListComponent extends HTMLList{

	protected function populateItem($entity){

		$this->addLabel("label", array(
			"text" => (isset($entity["label"])) ? $entity["label"] : ""
		));

		$this->addLabel("price", array(
			"text" => (isset($entity["price"])) ? soy2_number_format($entity["price"]) : ""
		));
	}
}
