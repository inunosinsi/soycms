<?php

class SearchCondtionButtonListComponent extends HTMLList {

	protected function populateItem($entity, $key, $counter, $length){

		$this->addLabel("button_html", array(
			"html" => (isset($entity)) ? $entity : ""
		));

		if(!strlen($entity)) return false;
	}
}
