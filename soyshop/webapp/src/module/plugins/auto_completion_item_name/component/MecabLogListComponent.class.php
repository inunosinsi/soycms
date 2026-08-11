<?php

class MecabLogListComponent extends HTMLList {

	protected function populateItem($entity){
		$this->addLink("log_link", array(
			"link" => (is_string($entity)) ? $entity : null,
			"text" => (is_string($entity)) ? trim(substr($entity, strrpos($entity, "/")), "/") : "",
			"target" => "_blank"
		));
	}
}
