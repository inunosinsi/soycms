<?php

class AddItemOrderFlagListComponent extends HTMLList {

	protected function populateItem($entity, $key){
		$this->addInput("number", array(
			"name" => "number[]",
			"value" => $key
		));

		$this->addInput("label", array(
			"name" => "label[]",
			"value" => (is_string($entity)) ? $entity : ""
		));

		return (is_string($entity));
	}
}
