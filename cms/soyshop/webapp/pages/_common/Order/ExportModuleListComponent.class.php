<?php

class ExportModuleListComponent extends HTMLList{
	
	private $exportPageLink;

	protected function populateItem($entity,$key){
		$this->addInput("module_id", array(
			/*"label" => "選択する",*/
			"name" => "plugin",
			"value" => $key,
		));

		$this->addLabel("export_title", array(
			"text" => $entity["title"],
		));

		$this->addLabel("export_description", array(
			"html" => $entity["description"],
			"visible" => (strlen($entity["description"]) > 0)
		));
		
		$this->addModel("display_button", array(
			"visible" => (strlen($entity["title"]))
		));
	}

	function getExportPageLink() {
		return $this->exportPageLink;
	}
	function setExportPageLink($exportPageLink) {
		$this->exportPageLink = $exportPageLink;
	}
}