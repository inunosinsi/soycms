<?php

class PlaceholderListComponent extends HTMLList{

	private $placeholderConfig;

	protected function populateItem($entity,$key){

		$this->addLabel("form_name", array(
			"text" => $entity
		));

		$this->addInput("placeholder", array(
			"name" => "Config[placeholderConfig][".$key."]",
			"value" => (is_string($key) && isset($this->placeholderConfig[$key])) ? $this->placeholderConfig[$key] : ""
		));
	}

	function setPlaceholderConfig(array $placeholderConfig){
		$this->placeholderConfig = $placeholderConfig;
	}
}
