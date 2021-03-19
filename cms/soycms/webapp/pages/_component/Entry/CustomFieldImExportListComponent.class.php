<?php

class CustomFieldImExportListComponent extends HTMLList{

	protected function populateItem($item, $fieldId){
		$this->addCheckBox("customfield_input", array(
			"label" => $item->getLabel(),
			"name" => "item[customfield(" . $fieldId . ")]",
			"value" => 1,
			"selected" => true
		));
	}
}
