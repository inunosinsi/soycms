<?php
/**
 * @class Point_methodList
 */
class PointMethodListComponent extends HTMLList{
	protected function populateItem($entity, $key, $counter, $length){
		$name = (isset($entity["name"]) && is_string($entity["name"])) ? $entity["name"] : "";
		$err = (isset($entity["error"]) && is_string($entity["error"])) ? $entity["error"] : "";

		$this->addLabel("point_name", array(
			"text" => $name
		));

		$this->addLabel("point_description", array(
			"html" => $entity["description"]
		));

		$this->addLabel("has_point_error", array(
			"visible" => (strlen($err) > 0)
		));
		$this->addLabel("point_error", array(
			"text" => $err
		));

		return (strlen($name) > 0);
	}
}
