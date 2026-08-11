<?php

class CustomFieldListComponent extends HTMLList{

	protected function populateItem($item){
		$this->addCheckBox("customfield_input", array(
			"label" => $item->getLabel(),
			"name" => "item[customfield(" . $item->getFieldId() . ")]",
			"value" => 1,
			"selected" => true
		));
	}
}
?>