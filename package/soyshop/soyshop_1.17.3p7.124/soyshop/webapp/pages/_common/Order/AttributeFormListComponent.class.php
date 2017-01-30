<?php

class AttributeFormListComponent extends HTMLList {

	protected function populateItem($item, $entity) {

		$this->addLabel("attribute_title", array(
			"text" => (isset($item["name"])) ? $item["name"] : ""
		));

		$this->addTextArea("attribute_value", array(
			"name" => "Attribute[" . $entity . "]",
			"value" => (isset($item["value"])) ? $item["value"] : "",
			"readonly" => (isset($item["readonly"]) && $item["readonly"] == true)
		));
	}
}
?>