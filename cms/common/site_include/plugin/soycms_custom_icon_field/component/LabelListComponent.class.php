<?php

class LabelListComponent extends HTMLList{

	private $cnf;

	function populateItem($entity, $key){
		$this->addCheckBox("label", array(
			"name" => "labels[]",
			"value" => $key,
			"label" => $entity,
			"selected" => (is_array($this->cnf) && count($this->cnf) && is_numeric(array_search($key, $this->cnf)))
		));
	}

	function setCnf($cnf){
		$this->cnf = $cnf;
	}
}
