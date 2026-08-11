<?php

class ItemOptionImExportListComponent extends HTMLList{

	protected function populateItem($entity, $key){
		$this->addCheckBox("item_option_input", array(
			"label" => (isset($entity["name"])) ? $entity["name"] : null,
			"name" => "item[item_option(" . $key . ")]",
			"value" => 1,
			"selected" => true
		));
	}
}
?>