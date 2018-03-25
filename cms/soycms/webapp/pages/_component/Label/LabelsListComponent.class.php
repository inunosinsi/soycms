<?php

class LabelsListComponent extends HTMLList{

	function populateItem($entity, $key){
		$this->addLabel("category_name", array(
			"text" => $key,
			"visible" => !is_int($key) && strlen($key),
		));
		$this->createAdd("list", "LabelListComponent", array(
			"list" => $entity
		));

		return ( count($entity) > 0 );
	}
}
