<?php

class TagCloudTagListComponent extends HTMLList {

	private $url;

	protected function populateItem($entity, $key, $int){
		$this->addLink("tag_cloud_tag_link", array(
			"soy2prefix" => "cms",
			"link" => (is_numeric($key)) ? $this->url . "?tagcloud=" . $key : ""
		));

		$this->addLabel("tag_cloud_tag_word", array(
			"soy2prefix" => "cms",
			"text" => (is_string($entity)) ? $entity : ""
		));
	}

	function setUrl($url){
		$this->url = $url;
	}
}
