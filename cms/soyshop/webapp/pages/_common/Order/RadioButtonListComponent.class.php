<?php

class RadioButtonListComponent extends HTMlList{

	private $name;
	private $selected;

	protected function populateItem($item,$key) {

		$this->addCheckBox("button", array(
			"value" => $key,
			"name" => $this->name,
			"label" => $item,
			"selected" => ($key == $this->selected)
		));
	}

	function getName() {
		return $this->name;
	}
	function setName($name) {
		$this->name = $name;
	}
	function getSelected() {
		return $this->selected;
	}
	function setSelected($selected) {
		$this->selected = $selected;
	}
}
?>