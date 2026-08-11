<?php

class PluginButtonListComponent extends HTMLList {

	protected function populateItem($entity, $key) {
		
		$title = (isset($entity["title"])) ? $entity["title"] : "";
		
		$this->addLabel("plugin_btn_title", array(
			"text" => $title
		));

		$this->addLink("plugin_btn_link", array(
			"link" => (isset($entity["link"])) ? $entity["link"] : null,
			"text" => (isset($entity["link_title"])) ? $entity["link_title"] : "",
			"target" => (isset($entity["target_blank"]) && $entity["target_blank"]) ? "_blank" : "_self",
			"attr:rel" => (isset($entity["target_blank"]) && $entity["target_blank"]) ? "noopener" : ""
		));
	}
}