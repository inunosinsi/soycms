<?php

class TopPagePluginAreaListComponent extends HTMLList{
	
	protected function populateItem($entity, $key){
		
		$this->addLabel("plugin_area_title", array(
			"text" => (isset($entity["title"])) ? $entity["title"] : ""
		));
		
		$this->addLink("plugin_area_link", array(
			"link" => (isset($entity["link"])) ? $entity["link"] : null,
			"text" => (isset($entity["link_title"])) ? $entity["link_title"] : ""
		));
		
		$this->addLabel("plugin_area_content", array(
			"html" => (isset($entity["content"])) ? $entity["content"] : ""
		));
	}		
}
?>