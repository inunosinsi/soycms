<?php
/**
 * @class Customfield_methodList
 */
class CustomfieldMethodListComponent extends HTMLList{
	protected function populateItem($entity, $key, $counter, $length){
		$this->addLabel("customfield_name", array(
			"text" => (isset($entity["name"])) ? $entity["name"] : ""
		));

		$this->addModel("customfield_is_required", array(
			"visible" => (isset($entity["isRequired"]) && $entity["isRequired"] == SOYShop_OrderAttribute::IS_REQUIRED),
			"class" => "require"
		));

		$this->addLabel("customfield_description", array(
			"html" => (isset($entity["description"])) ? $entity["description"] : ""
		));

		$this->addLabel("customfield_annotation", array(
			"html" => (isset($entity["annotation"])) ? $entity["annotation"] : ""
		));

		$this->addModel("has_customfield_error", array(
			"visible" => (isset($entity["error"]) && strlen($entity["error"]) > 0)
		));
		$this->addLabel("customfield_error", array(
			"text" => (isset($entity["error"])) ? $entity["error"] : ""
		));
	}
}
