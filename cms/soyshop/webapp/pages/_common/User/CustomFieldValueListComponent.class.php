<?php

class CustomFieldValueListComponent extends HTMLList{

	protected function populateItem($item) {
		
		$this->addLabel("customfield_title", array(
			"text" => (isset($item["name"])) ? $item["name"] : ""
		));
		
		$val = (isset($item["value"])) ? nl2br(htmlspecialchars($item["value"], ENT_QUOTES, "UTF-8"))  : "";
		if(isset($item["style"])){
			$val = "<span style=\"" . $item["style"] . "\">" . $val . "</span>";
		}

		$this->addLabel("customfield_value", array(
			"html" => $val
		));
	}
}
?>