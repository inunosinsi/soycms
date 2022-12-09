<?php

class PriceListComponent extends HTMLList {

	function populateItem($entity){
		$this->addLabel("label", array(
			"text" => (isset($entity["label"])) ? $entity["label"] : ""
		));

		$this->addLabel("ext_price", array(
			"text" => (isset($entity["price"])) ? soy2_number_format($entity["price"]) : ""
		));

		if(!isset($entity["label"]) || !isset($entity["price"]) || (int)$entity["price"] === 0) return false;
	}
}
