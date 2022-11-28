<?php

class TemplateTypeListComponent extends HTMLList{

	private $selected;

	protected function populateItem($entity, $key){
		$this->addCheckBox("type", array(
			"name" => "Template[type]",
			"value" => $key,
			"label" => $entity,
			"selected" => ($this->getSelected() == $key)
		));
	}


	function getSelected() {
		return $this->selected;
	}
	function setSelected($selected) {
		$this->selected = $selected;
	}
}
?>