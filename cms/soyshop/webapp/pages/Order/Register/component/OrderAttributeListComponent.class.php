<?php

class OrderAttributeListComponent extends HTMLList{
	protected function populateItem($entity){
		$this->addLabel("attribute_title", array(
			"text" => (isset($entity["name"])) ? $entity["name"] : "",
		));
		$this->addLabel("attribute_value", array(
			"text" => (isset($entity["value"])) ? $entity["value"] : "",
		));
	}
}
