<?php

class CategoryListComponent extends HTMLList {

	function populateItem($entity, $key, $index){

		$this->addLabel("id", array(
			"text" => $entity->getId()
		));

		$this->addLabel("name", array(
			"text" => $entity->getName()
		));

		$this->addLabel("prefix", array(
			"text" => $entity->getPrefix()
		));

		$this->addLink("detail_link", array(
			"link" => "javascript:void(0)",
			"onclick" => "$(\".category_detail_" . $entity->getId() . "\").toggle();"
		));

		$this->addInput("input_id", array(
			"name" => "Edit[id]",
			"value" => $entity->getId()
		));

		$this->addInput("input_name", array(
			"name" => "Edit[name]",
			"value" => $entity->getName(),
			"attr:required" => "required"
		));

		$this->addInput("input_prefix", array(
			"name" => "Edit[prefix]",
			"value" => $entity->getPrefix()
		));
	}
}
