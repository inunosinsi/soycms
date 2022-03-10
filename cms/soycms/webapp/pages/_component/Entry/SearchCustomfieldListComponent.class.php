<?php

class SearchCustomfieldListComponent extends HTMLList{

	private $conditions = array();

	protected function populateItem($entity, $key){
		$fieldId = (is_string($key)) ? $key : "";

		$this->addLabel("field_label", array(
			"text" => (is_string($entity)) ? $entity : ""
		));

		$this->addInput("field_input", array(
			"name" => "customfield[" . (string)$key . "]",
			"value" => (is_array($this->conditions) && strlen($fieldId) && isset($this->conditions[$fieldId])) ? $this->conditions[$fieldId] : ""
		));
	}

	function setConditions($conditions){
		$this->conditions = $conditions;
	}
}