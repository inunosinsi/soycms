<?php

class CategorizedLabelListComponent extends HTMLList{
	private $selectedLabelList = array();

	public function populateItem($entity, $key){
		$this->addLabel("category_name",array(
			"text" => $key,
		));
		$this->createAdd("labels", "_component.Entry.Detail.LabelListComponent", array(
			"list" => $entity,
			"selectedLabelList" => $this->selectedLabelList
		));
	}

	public function setSelectedLabelList($array){
		if(is_array($array)) $this->selectedLabelList = $array;
	}
}