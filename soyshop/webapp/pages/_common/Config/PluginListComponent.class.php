<?php

class PluginListComponent extends HTMLList{

	private $configPageLink;

	protected function populateItem($entity,$key){
		$this->addLink("config_page_link", array(
			"text" => $entity["title"],
			"link" => $this->configPageLink . "?plugin=" . $key
		));

		$dsp = (is_string($entity["description"]) && strlen($entity["description"])) ? trim($entity["description"]) : "";
		$this->addLabel("config_page_description", array(
			"html" => $dsp,
			"visible" => (strlen($dsp) > 0)
		));
	}

	function getConfigPageLink() {
		return $this->configPageLink;
	}
	function setConfigPageLink($configPageLink) {
		$this->configPageLink = $configPageLink;
	}
}
