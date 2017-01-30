<?php

class AttributeListComponent extends HTMLList {

	protected function populateItem($item, $key) {
		$this->addLabel("attribute_title", array(
			"text" => (isset($item["name"])) ? $item["name"] : "",
			"title" => (isset($item["name"])) ? $item["name"]." (" . $key . ")" : ""
		));

		$this->addLabel("attribute_value", array(
			"html" => (isset($item["value"])) ? nl2br(htmlspecialchars($item["value"], ENT_QUOTES, "UTF-8")) : ""
		));
	}
}
?>