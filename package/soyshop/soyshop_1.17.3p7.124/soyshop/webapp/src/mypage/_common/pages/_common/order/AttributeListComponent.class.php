<?php

class AttributeListComponent extends HTMLList{
	
	protected function populateItem($item) {

		$this->addLabel("attribute_title", array(
			"text" => (isset($item["name"])) ? $item["name"] : ""
		));

		$this->addLabel("attribute_value", array(
			"html" => (isset($item["value"])) ? nl2br(htmlspecialchars($item["value"], ENT_QUOTES, "UTF-8")) : ""
		));

		if(isset($item["hidden"]) && $item["hidden"])return false;
	}
}
?>