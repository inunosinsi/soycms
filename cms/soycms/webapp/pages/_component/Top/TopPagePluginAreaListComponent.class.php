<?php

class TopPagePluginAreaListComponent extends HTMLList{

	protected function populateItem($entity, $key){

		$title = (isset($entity["title"])) ? $entity["title"] : "";
		$content = (isset($entity["content"])) ? $entity["content"] : "";

		$this->addLabel("plugin_area_title", array(
			"text" => $title
		));

		// $this->addLink("plugin_area_link", array(
		// 	"link" => (isset($entity["link"])) ? $entity["link"] : null,
		// 	"text" => (isset($entity["link_title"])) ? $entity["link_title"] : "",
		// 	"target" => (isset($entity["target_blank"]) && $entity["target_blank"]) ? "_blank" : "_self"
		// ));

		$this->addLabel("plugin_area_content", array(
			"html" => $content
		));

		if(!strlen($title) && !strlen($content)) return false;
	}
}
