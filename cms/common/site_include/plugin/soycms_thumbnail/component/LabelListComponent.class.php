<?php

class LabelListComponent extends HTMLList{

	private $paths;

	protected function populateItem($entity){
		
		$path = (isset($this->paths[$entity->getId()])) ? trim($this->paths[$entity->getId()]) : null;

		$this->addLabel("caption", array(
			"text" => $entity->getCaption()
		));

		$this->addInput("label_thumbnail_path", array(
			"name" => "Config[label_thumbnail_path][" . $entity->getId() . "]",
			"value" => $path,
			"id" => "label_thumbnail_path_" . $entity->getId(),
			"style" => "width:50%"
		));

		$this->addLabel("label_thumbnail_path_id", array(
			"text" => "label_thumbnail_path_" . $entity->getId()
		));

		$this->addModel("display_label_image_ppreview_button", array(
			"visible" => (isset($path))
		));
	}

	function setPaths($paths){
		$this->paths = $paths;
	}
}
?>
