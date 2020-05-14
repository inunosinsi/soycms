<?php

class PluginListComponent extends HTMLList{

	private $configPageLink;

	protected function populateItem($entity,$key){
		$this->addLink("config_page_link", array(
			"text" => $entity["title"],
			"link" => $this->configPageLink . "?plugin=" . $key
		));

		$this->addLabel("config_page_description", array(
			"html" => $entity["description"],
			"visible" => (strlen($entity["description"]) > 0)
		));
	}

	function getConfigPageLink() {
		return $this->configPageLink;
	}
	function setConfigPageLink($configPageLink) {
		$this->configPageLink = $configPageLink;
	}
}
