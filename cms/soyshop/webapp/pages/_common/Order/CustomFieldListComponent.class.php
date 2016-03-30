<?php

class CustomfieldListComponent extends HTMLList {

	protected function populateItem($item) {

		$this->addLabel("customfield_title", array(
			"text" => (isset($item["name"])) ? $item["name"] : ""
		));

		$this->addLabel("customfield_value", array(
			"html" => (isset($item["value"])) ? nl2br(htmlspecialchars($item["value"], ENT_QUOTES, "UTF-8"))  : ""
		));
	}
}
?>