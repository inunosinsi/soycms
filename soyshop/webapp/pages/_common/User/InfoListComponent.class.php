<?php

class InfoListComponent extends HTMLList{
	
	protected function populateItem($entity, $key){
		
		$this->addLabel("info_title", array(
			"html" => (isset($entity["title"]) && strlen($entity["title"]) > 0) ? $entity["title"] : ""
		));
				
		$this->addLabel("info_description", array(
			"html" => (isset($entity["description"])) ? nl2br($entity["description"]) : ""
		));
		
	}
}
?>