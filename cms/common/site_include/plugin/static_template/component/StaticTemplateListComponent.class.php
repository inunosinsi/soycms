<?php

class StaticTemplateListComponent extends HTMLList {

	private $dir;

	protected function populateItem($entity){
		if(!is_array($entity)) $entiry = array();

		$this->addLabel("name", array(
			"text" => (isset($entity["name"])) ? $entity["name"] : ""	
		));

		$this->addLabel("type", array(
			"text" => (isset($entity["type"])) ? $entity["type"] : ""
		));

		$this->addLabel("filepath", array(
			"text" => (isset($entity["filename"])) ? $this->dir.$entity["filename"] : ""	
		));
	}

	function setDir(string $dir){
		$this->dir = $dir;
	}
}
