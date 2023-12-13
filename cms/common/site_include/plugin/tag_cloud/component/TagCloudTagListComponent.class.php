<?php

class TagCloudTagListComponent extends HTMLList {

	private $url;

	protected function populateItem($entity, $id, $int){
		$word = (isset($entity["word"]) && is_string($entity["word"])) ? $entity["word"] : "";

		$this->addLink("tag_cloud_tag_link", array(
			"soy2prefix" => "cms",
			//"link" => (is_numeric($id)) ? $this->url . "?tagcloud=" . $id : ""
			"link" => $this->url . "/" . $word
		));

		$hash = (isset($entity["hash"]) && is_string($entity["hash"])) ? trim($entity["hash"]) : "";
		$this->addLink("tag_cloud_tag_hash_link", array(
			"soy2prefix" => "cms",
			//"link" => (strlen($hash)) ? $this->url . "?tagcloud=" . $hash : ""
			"link" => $this->url . "/" . $hash
		));

		$this->addLabel("tag_cloud_tag_get_param", array(
			"soy2prefix" => "cms",
			"text" => (is_numeric($id)) ? "?tagcloud=" . $id : ""
		));

		$this->addLabel("tag_cloud_tag_hash_get_param", array(
			"soy2prefix" => "cms",
			"text" => (strlen($hash)) ? "?tagcloud=" . $hash : ""
		));

		$this->addLabel("tag_cloud_tag_word", array(
			"soy2prefix" => "cms",
			"text" => $word
		));
	}

	function setUrl($url){
		$this->url = $url;
	}
}
