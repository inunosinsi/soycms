<?php

class GalleryListComponent extends HTMLList{
	
	protected function populateItem($entity){
		
		$this->addLink("name", array(
			"text" => $entity->getName(),
			"link" => SOY2PageController::createLink(APPLICATION_ID . ".List." . $entity->getId())
		));
		
		$this->addLabel("name_plain", array(
			"text" => $entity->getName(),
		));
		
		$this->addLabel("gallery_id", array(
			"text" => $entity->getGalleryId()
		));
		
		$this->addLabel("memo", array(
			"text" => $entity->getMemo()
		));
		
		$this->addLink("list_link", array(
			"link" => SOY2PageController::createLink(APPLICATION_ID . ".List." . $entity->getId())
		));
		
		$this->addLink("detail_link", array(
			"link" => SOY2PageController::createLink(APPLICATION_ID . ".Gallery.Detail." . $entity->getId())
		));
		
		$this->addLink("new_list_link", array(
			"link" => SOY2PageController::createLink(APPLICATION_ID . ".List.Ajax." . $entity->getId())
		));
		
		$this->addActionLink("remove_link", array(
			"link" => SOY2PageController::createLink(APPLICATION_ID . ".Gallery.Remove." . $entity->getId()),
			"onclick" => "return confirm('削除してもよろしいですか？');"
		));
	}
}
?>