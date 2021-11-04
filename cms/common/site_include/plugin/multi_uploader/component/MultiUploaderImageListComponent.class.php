<?php

class MultiUploaderImageListComponent extends HTMLList {

	private $alts;

	protected function populateItem($entity, $key){
		$hash = (is_string($entity) && strlen($entity)) ? MultiUploaderUtil::path2Hash($entity) : "";
		$path = (strlen($hash)) ? trim($entity) : "";
		$alt = (strlen($hash) && isset($this->alts[$hash])) ? $this->alts[$hash] : "";

		$this->addImage("image", array(
			"soy2prefix" => "cms",
			"src" => $path,
			"alt" => $alt
		));

		$this->addLink("image_link", array(
			"soy2prefix" => "cms",
			"link" => $path
		));

		$this->addLabel("image_path", array(
			"soy2prefix" => "cms",
			"text" => $path
		));

		$this->addLabel("image_alt", array(
			"soy2prefix" => "cms",
			"text" => $alt
		));

		if(!strlen($hash)) return false;
	}

	function setAlts($alts){
		$this->alts = $alts;
	}
}
