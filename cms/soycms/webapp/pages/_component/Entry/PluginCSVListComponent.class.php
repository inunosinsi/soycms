<?php

class PluginCSVListComponent extends HTMLList{

	protected function populateItem($entity, $pluginId){
		$label = (is_string($entity)) ? $entity : null;

		$this->addLabel("label", array(
			"text" => $label
		));

		$this->addCheckBox("checkbox", array(
			"label" => $label,
			"name" => "item[plugins($pluginId)]",
			"value" => 1,
			"selected" => true
		));
	}
}
