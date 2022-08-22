<?php

class DlListFieldListComponent extends HTMLList {

	function populateItem($entity, $i){

		$this->addLabel("label", array(
			"soy2prefix" => "cms",
			"text" => (is_array($entity) && isset($entity["label"])) ? $entity["label"] : ""
		));

		$this->addLabel("dt", array(
			"soy2prefix" => "cms",
			"text" => (is_array($entity) && isset($entity["label"])) ? $entity["label"] : ""
		));

		$this->addLabel("value", array(
			"soy2prefix" => "cms",
			"text" => (is_array($entity) && isset($entity["value"])) ? $entity["value"] : ""
		));

		$this->addLabel("dd", array(
			"soy2prefix" => "cms",
			"text" => (is_array($entity) && isset($entity["value"])) ? $entity["value"] : ""
		));
	}
}
