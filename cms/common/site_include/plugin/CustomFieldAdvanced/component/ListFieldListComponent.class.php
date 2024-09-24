<?php

class ListFieldListComponent extends HTMLList {

	private $extraValues;

	function populateItem($entity, $i){
		$v = (is_string($entity)) ? $entity : "";

		$this->addLabel("li", array(
			"soy2prefix" => "cms",
			"text" => $v
		));

		$this->addLabel("li_raw", array(
			"soy2prefix" => "cms",
			"html" => $v
		));

		$this->addLabel("value", array(
			"soy2prefix" => "cms",
			"text" => $v
		));

		//画像
		$isImageFile = (strlen($v) && soycms_check_is_image_path($v));

		$this->addModel("is_image", array(
			"soy2prefix" => "cms",
			"visible" => $isImageFile
		));

		$this->addModel("no_image", array(
			"soy2prefix" => "cms",
			"visible" => !$isImageFile
		));

		$this->addImage("image", array(
			"soy2prefix" => "cms",
			"src" => ($isImageFile) ? $v : "",
			"attr:alt" => (is_numeric($i) && isset($this->extraValues["alt"][$i])) ? $this->extraValues["alt"][$i] : $v
		));

		$this->addLink("target_link", array(
			"soy2prefix" => "cms",
			"link" => ($isImageFile && is_numeric($i) && isset($this->extraValues["url"][$i])) ? $this->extraValues["url"][$i] : "",
			"target" => ($isImageFile && is_numeric($i) && isset($this->extraValues["target"][$i])) ? $this->extraValues["target"][$i] : "_self"
		));

		$this->addLink("image_link", array(
			"soy2prefix" => "cms",
			"link" => ($isImageFile) ? $v : "",
		));

		$this->addLabel("image_text", array(
			"soy2prefix" => "cms",
			"text" => ($isImageFile) ? $v : ""
		));
	}

	function setExtraValues(array $extraValues){
		$this->extraValues = $extraValues;
	}
}
