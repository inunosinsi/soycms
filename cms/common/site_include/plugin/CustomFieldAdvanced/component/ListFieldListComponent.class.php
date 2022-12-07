<?php

class ListFieldListComponent extends HTMLList {

	function populateItem($entity, $i){
		$v = (is_string($entity)) ? $entity : "";

		$this->addLabel("li", array(
			"soy2prefix" => "cms",
			"text" => $v
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
			"src" => ($isImageFile) ? $v : ""
		));

		$this->addLink("image_link", array(
			"soy2prefix" => "cms",
			"link" => ($isImageFile) ? $v : ""
		));

		$this->addLabel("image_text", array(
			"soy2prefix" => "cms",
			"text" => ($isImageFile) ? $v : ""
		));
	}
}
