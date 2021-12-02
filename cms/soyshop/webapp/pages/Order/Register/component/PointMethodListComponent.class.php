<?php

class PointMethodListComponent extends HTMLList{

	protected function populateItem($entity, $key, $counter, $length){
		$name = (isset($entity["name"]) && is_string($entity["name"])) ? $entity["name"] : "";
		$this->addLabel("point_name", array(
				"text" => $name
		));

		$this->addLabel("point_description", array(
				"html" => $entity["description"]
		));

		$err = (is_string($entity["error"])) ? $entity["error"] : "";
		$this->addModel("has_point_error", array(
				"visible" => (strlen($err) > 0)
		));
		$this->addLabel("point_error", array(
				"text" => $err
		));

		return (strlen($name) > 0);
	}
}
