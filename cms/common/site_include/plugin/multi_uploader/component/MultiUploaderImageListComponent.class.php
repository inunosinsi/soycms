<?php

class MultiUploaderImageListComponent extends HTMLList {

	protected function populateItem($entity){

		$this->addImage("image", array(
			"soy2prefix" => "cms",
			"src" => (is_string($entity)) ? $entity : ""
		));

		$this->addLink("image_link", array(
			"soy2prefix" => "cms",
			"link" => (is_string($entity)) ? $entity : ""
		));

		$this->addLabel("image_path", array(
			"soy2prefix" => "cms",
			"text" => (is_string($entity)) ? $entity : ""
		));

		if(!is_string($entity)) return false;
	}
}
