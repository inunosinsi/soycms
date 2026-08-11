<?php

class PageListComponent extends HTMLList{
	
	private $displayConfig;
	
	protected function populateItem($entity, $key){
		
		$this->addCheckBox("page", array(
			"name" => "display_config[]",
			"value" => $key,
			"selected" => (isset($this->displayConfig[$key]) && $this->displayConfig[$key] == 1),
			"label" => (isset($entity)) ? $entity : ""
		));
	}
	
	function setDisplayConfig($displayConfig){
		$this->displayConfig = $displayConfig;
	}
}
?>