<?php

class CustomFieldFormListComponent extends HTMLList {

	protected function populateItem($entity, $key) {
		$this->addLabel("customfield_title", array(
			"text" => (isset($entity["label"])) ? $entity["label"] : ""
		));

		$this->addLabel("customfield_value", array(
			"html" => (isset($entity["form"])) ? $entity["form"] : "",
		));
	}
}
?>