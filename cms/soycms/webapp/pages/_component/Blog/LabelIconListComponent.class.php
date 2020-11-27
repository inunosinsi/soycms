<?php

class LabelIconListComponent extends HTMLList{

	function populateItem($entity){
		$this->addImage("image_list_icon", array(
			"src" => $entity->url,
			"ondblclick" => "javascript:postChangeLabelIcon('".$entity->filename."');"
		));
	}
}
