<?php
class CustomSearchFieldImExportListComponent extends HTMLList{

    protected function populateItem($item, $fieldId){
		$this->addCheckBox("custom_search_field_input", array(
            "label" => (is_array($item) && isset($item["label"])) ? $item["label"] : "",
            "name" => (is_string($fieldId)) ? "item[custom_search_field(" . $fieldId . ")]" : "",
            "value" => 1,
            "selected" => true
        ));
    }
}
