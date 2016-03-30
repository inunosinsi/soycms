<?php

class CustomFieldFormListComponent extends HTMLList{

	protected function populateItem($entity, $key, $counter, $length){
		$this->addLabel("customfield_name", array(
			"text" => (isset($entity["name"])) ? $entity["name"] : ""
		));

		$this->addLabel("customfield_form", array(
			"html" => (isset($entity["form"])) ? $entity["form"] : ""
		));
	}
}
?>