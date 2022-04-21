<?php

class AddressItemsListComponent extends HTMLList {

	protected function populateItem($entity, $key){
	
		$this->addLabel("item_number", array(
			"text" => (is_numeric($key)) ? "項目" . ($key + 1) : ""
		));

		$this->addInput("label", array(
			"name" => "Config[" . $key . "][label]",
			"value" => (isset($entity["label"])) ? $entity["label"] : ""
		));

		$this->addCheckBox("required", array(
			"name" => "Config[" . $key . "][required]",
			"value" => 1,
			"selected" => (isset($entity["required"]) && (bool)$entity["required"])
		));

		$this->addInput("example", array(
			"name" => "Config[" . $key . "][example]",
			"value" => (isset($entity["example"])) ? $entity["example"] : ""
		));
	}
}