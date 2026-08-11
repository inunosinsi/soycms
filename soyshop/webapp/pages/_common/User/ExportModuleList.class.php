<?php

class ExportModuleList extends HTMLList{

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

		$dsp = (isset($entity["description"]) && is_string($entity["description"])) ? $entity["description"] : "";
		$this->addLabel("export_description", array(
			"html" => $dsp,
			"visible" => (strlen($dsp) > 0)
		));
	}

	function getExportPageLink() {
		return $this->exportPageLink;
	}
	function setExportPageLink($exportPageLink) {
		$this->exportPageLink = $exportPageLink;
	}
}
