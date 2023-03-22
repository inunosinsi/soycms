<?php

class ThListComponent extends HTMLList{

	private $languages = array();

	protected function populateItem($entity){
		$this->addLabel("lang", array(
			"text" => (is_string($entity)) ? $entity : ""
		));
	}

	function setLanguages($languages){
		$this->languages = $languages;
	}
}
