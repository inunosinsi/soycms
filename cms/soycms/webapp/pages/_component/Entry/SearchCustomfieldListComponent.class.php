<?php

class SearchCustomfieldListComponent extends HTMLList{

	private $conditions = array();
	private $last;

	protected function populateItem($entity, $key, $int){
		$fieldId = (is_string($key)) ? $key : "";

		$this->addLabel("field_label", array(
			"text" => (is_string($entity)) ? $entity : ""
		));

		$this->addModel("colspan_last", array(
			"attr:colspan" => ($int == $this->last && $this->last%2 === 1) ? "3" : "1"
		));

		$this->addInput("field_input", array(
			"name" => "customfield[" . $fieldId . "]",
			"value" => (is_array($this->conditions) && strlen($fieldId) && isset($this->conditions[$fieldId])) ? $this->conditions[$fieldId] : ""
		));
	}

	function setConditions($conditions){
		$this->conditions = $conditions;
	}
	function setLast($last){
		$this->last = $last;
	}
}