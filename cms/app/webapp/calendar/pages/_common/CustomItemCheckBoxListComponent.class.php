<?php

class CustomItemCheckBoxListComponent extends HTMLList{

    private $checkedList;

	protected function populateItem($entity, $id){
		
        $this->addCheckBox("label", array(
            "name" => "Custom[]",
            "value" => $id,
            "selected" => (is_numeric($id) && is_numeric(array_search($id, $this->checkedList))),
            "label" => (is_string($entity)) ? $entity : ""
        ));
	}

    function setCheckedList($checkedList){
		$this->checkedList = $checkedList;
    }
}
