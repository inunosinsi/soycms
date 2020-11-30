<?php
class ImageListComponent extends HTMLList{

	protected function populateItem($entity, $key){
		$this->addImage("image", array(
			"src" => $entity
		));
	}
}
