<?php

class UserAttributeListComponent extends HTMLList {

	protected function populateItem($item, $key) {
		if(!is_string($key)) $key = "";

		$this->addLabel("attribute_title", array(
			"text" => (isset($item["name"])) ? $item["name"] : "",
			"title" => (isset($item["name"])) ? $item["name"]." (" . $key . ")" : ""
		));

		$this->addLabel("attribute_value", array(
			"html" => (isset($item["value"])) ? nl2br(htmlspecialchars($item["value"], ENT_QUOTES, "UTF-8")) : ""
		));
	}
}
