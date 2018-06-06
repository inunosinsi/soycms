<?php

class GravatarProfileListComponent extends HTMLList {

	private $thumbnailSize;
	private $url;

	protected function populateItem($entity){
		$this->addImage("gravatar_thumbnail", array(
			"soy2prefix" => "gra",
			"src" => (isset($entity["thumbnailUrl"])) ? $entity["thumbnailUrl"] . ".jpg?s=" . $this->thumbnailSize : ""
		));

		$this->addLink("gravatar_link", array(
			"soy2prefix" => "gra",
			"link" => (isset($entity["profileUrl"])) ? $entity["profileUrl"] : ""
		));

		$this->addLink("list_page_link", array(
			"soy2prefix" => "gra",
			"link" => (isset($entity["alias"])) ? $this->url . $entity["alias"] : ""
		));

		$this->addLabel("name", array(
			"soy2prefix" => "gra",
			"text" => (isset($entity["name"])) ? $entity["name"] : ""
		));

		$this->addLabel("reading", array(
			"soy2prefix" => "gra",
			"text" => (isset($entity["reading"])) ? $entity["reading"] : ""
		));

		$this->addLabel("display_name", array(
			"soy2prefix" => "gra",
			"text" => (isset($entity["displayname"])) ? $entity["displayname"] : ""
		));

		$this->addLabel("fullname", array(
			"soy2prefix" => "gra",
			"text" => (isset($entity["fullname"])) ? $entity["fullname"] : ""
		));

		$this->addLabel("about_me", array(
			"soy2prefix" => "gra",
			"html" => (isset($entity["aboutMe"])) ? nl2br(htmlspecialchars($entity["aboutMe"], ENT_QUOTES, "UTF-8")) : ""
		));
	}

	function setThumbnailSize($thumbnailSize){
		$this->thumbnailSize = $thumbnailSize;
	}

	function setUrl($url){
		$this->url = $url;
	}
}
