<?php
/**
 * @class Point_methodList
 */
class PointMethodListComponent extends HTMLList{
	protected function populateItem($entity, $key, $counter, $length){
		$this->addLabel("point_name", array(
			"text" => $entity["name"]
		));

		$this->addLabel("point_description", array(
			"html" => $entity["description"]
		));

		$this->addLabel("has_point_error", array(
			"visible" => (strlen($entity["error"]) > 0)
		));
		$this->addLabel("point_error", array(
			"text" => $entity["error"]
		));
	}
}
?>