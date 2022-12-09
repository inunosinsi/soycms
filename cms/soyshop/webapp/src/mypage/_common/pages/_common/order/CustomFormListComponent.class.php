<?php

class CustomFormListComponent extends HTMLList {

	protected function populateItem($entity) {

		$this->addLabel("label", array(
			"text" => (isset($entity["label"])) ? $entity["label"] : ""
		));

		$this->addLabel("form", array(
			"html" => (isset($entity["form"])) ? $entity["form"] : ""
		));
	}
}
