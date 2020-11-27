<?php

class LabelListComponent extends HTMLList{

	private $paths;

	protected function populateItem($entity){
		$id = (is_numeric($entity->getId())) ? (int)$entity->getId() : 0;
		$path = (isset($this->paths[$id])) ? trim($this->paths[$id]) : null;

		$this->addLabel("caption", array(
			"text" => $entity->getCaption()
		));

		$this->addInput("label_thumbnail_path", array(
			"name" => "Config[label_thumbnail_path][" . $id . "]",
			"value" => $path,
			"id" => "label_thumbnail_path_" . $id,
			"style" => "width:50%"
		));

		$this->addLabel("label_thumbnail_path_id", array(
			"text" => "label_thumbnail_path_" . $id
		));

		$this->addModel("display_label_image_ppreview_button", array(
			"visible" => (isset($path))
		));
	}

	function setPaths($paths){
		$this->paths = $paths;
	}
}
