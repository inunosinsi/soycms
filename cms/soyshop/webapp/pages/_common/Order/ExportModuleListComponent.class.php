<?php

class ExportModuleListComponent extends HTMLList{

	private $exportPageLink;

	protected function populateItem($entity,$key){
		$title = (isset($entity["title"]) && is_string($entity["title"])) ? $entity["title"] : "";
		$dsp = (isset($entity["description"]) && is_string($entity["description"])) ? $entity["description"] : "";

		$this->addInput("module_id", array(
			/*"label" => "選択する",*/
			"name" => "plugin",
			"value" => $key,
		));

		$this->addLabel("export_title", array(
			"text" => $title,
		));

		$this->addLabel("export_description", array(
			"html" => $dsp,
			"visible" => (strlen($dsp) > 0)
		));

		$this->addModel("display_button", array(
			"visible" => (strlen($title) > 0)
		));
	}

	function getExportPageLink() {
		return $this->exportPageLink;
	}
	function setExportPageLink($exportPageLink) {
		$this->exportPageLink = $exportPageLink;
	}
}
