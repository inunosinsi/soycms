<?php

class DlListFieldListComponent extends HTMLList {

	function populateItem($entity, $i){

		$this->addLabel("label", array(
			"soy2prefix" => "cms",
			"text" => (is_array($entity) && isset($entity["label"])) ? $entity["label"] : ""
		));

		$this->addLabel("dt", array(
			"soy2prefix" => "cms",
			"text" => (is_array($entity) && isset($entity["label"])) ? $entity["label"] : ""
		));

		$v = (is_array($entity) && isset($entity["value"])) ? $entity["value"] : "";

		$this->addLabel("value", array(
			"soy2prefix" => "cms",
			"text" => $v
		));

		$this->addLabel("dd", array(
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
	}
}
