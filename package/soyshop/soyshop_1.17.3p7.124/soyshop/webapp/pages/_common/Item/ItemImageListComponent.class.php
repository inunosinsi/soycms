<?php

class ItemImageListComponent extends HTMLList{

	protected function populateItem($entity,$key){
		$isImage = preg_match('/\.(jpg|jpeg|png|gif)$/i', $entity);
		$this->addImage("image", array(
			"src" => $entity,
		));
		
		if(!$isImage){
			return false;
		}
	}
}
?>