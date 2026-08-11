<?php

class UserCustomSearchFieldImExportListComponent extends HTMLList{

	protected function populateItem($item, $fieldId){

		$this->addCheckBox("custom_search_field_input", array(
			"label" => (isset($item["label"])) ? $item["label"] : null,
			"name" => "item[custom_search_field(" . $fieldId . ")]",
			"value" => 1,
			"selected" => true
		));
	}
}
?>