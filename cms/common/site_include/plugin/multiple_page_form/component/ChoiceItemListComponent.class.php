<?php

class ChoiceItemListComponent extends HTMLList {

	private $pages;

	protected function populateItem($entity){
		$this->addInput("item", array(
			"name" => "Config[Item][]",
			"value" => (isset($entity["item"])) ? $entity["item"] : ""
		));

		$this->addInput("order", array(
			"name" => "Config[Order][]",
			"value" => (isset($entity["order"])) ? $entity["order"] : ""
		));

		$this->addSelect("page_type", array(
			"name" => "Config[Next][]",
			"options" => $this->pages,
			"selected" => (isset($entity["next"])) ? $entity["next"] : ""
		));
	}

	function setPages($pages){
		$this->pages = $pages;
	}
}
