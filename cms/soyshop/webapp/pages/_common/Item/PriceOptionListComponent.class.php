<?php

class PriceOptionListComponent extends HTMLList{

	protected function populateItem($entity){

		$this->addLabel("title", array(
			"text" => (isset($entity["title"])) ? $entity["title"] : ""
		));

		$this->addLabel("form", array(
			"html" => (isset($entity["form"])) ? $entity["form"] : ""
		));
	}
}
