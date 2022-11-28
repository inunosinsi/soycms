<?php

class CustomSearchItemListComponent extends HTMLList {

	protected function populateItem($item) {

		$this->addLabel("label", array(
			"text" => (isset($item["label"])) ? $item["label"] : ""
		));

		$this->addLabel("form", array(
			"html" => (isset($item["form"])) ? $item["form"] : ""
		));
	}
}
