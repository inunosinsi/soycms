<?php

class PageListComponent extends HTMLList{

	private $pluginObj;

	function populateItem($entity){

		$this->addCheckBox("page_item", array(
			"type"     => "checkbox",
			"name"     => "config_per_page[".$entity->getId()."]",
			"value"    => 1,
			"selected" => (isset($this->pluginObj->config_per_page[$entity->getId()])),
			"label"    => $entity->getTitle() . " (/{$entity->getUri()})"
		));

		if(!is_numeric($entity->getPageType())) return false;
		if($entity->getPageType() == Page::PAGE_TYPE_ERROR || $entity->getPageType() == Page::PAGE_TYPE_APPLICATION) return false;
	}


	function setPluginObj($pluginObj) {
		$this->pluginObj = $pluginObj;
	}
}
