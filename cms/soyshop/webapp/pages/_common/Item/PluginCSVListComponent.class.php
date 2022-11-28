<?php

class PluginCSVListComponent extends HTMLList{

	protected function populateItem($array, $key){
		$this->addLabel("label", array(
			"text" => $array["label"]
		));

		$this->addCheckBox("checkbox", array(
			"label" => $array["label"],
			"name" => "item[plugins($key)]",
			"value" => 1,
			"selected" => true
		));
	}
}
