<?php

class ListFieldListComponent extends HTMLList {

	function populateItem($entity, $i){
		$this->addLabel("value", array(
			"soy2prefix" => "cms",
			"text" => (is_string($entity)) ? $entity : ""
		));
	}
}
