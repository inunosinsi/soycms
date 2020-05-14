<?php

class AddItemOrderFlagListComponent extends HTMLList {

	protected function populateItem($entity, $key){
		$this->addInput("number", array(
			"name" => "number[]",
			"value" => (isset($key)) ? $key : 0
		));

		$this->addInput("label", array(
			"name" => "label[]",
			"value" => (isset($entity)) ? $entity : ""
		));
	}
}
