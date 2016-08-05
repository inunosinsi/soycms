<?php
class NewImageListComponent extends HTMLList{
	
	protected function populateItem($entity){
		
		$this->addImage("image", array(
			"src" => SOY_GALLERY_IMAGE_ACCESS_PATH . $entity->getGalleryId() . "/" . "t_".$entity->getFilename()
		));
		
		$this->addLabel("memo", array(
			"html" => nl2br($entity->getMemo())
		));
		
		$this->addLink("name", array(
			"text" => $entity->getName(),
			"link" => SOY2PageController::createLink(APPLICATION_ID . ".List." . $entity->getGId())
		));
		
		$this->addLabel("is_public", array(
			"text" => ($entity->getIsPublic() == SOYGallery_ImageView::IS_PUBLIC) ? "公開" : "非公開"
		));
		
		$this->addLabel("create_date", array(
			"text" => date("Y/m/d H:i:s", $entity->getCreateDate())
		));
		
		$this->addLink("detail_link", array(
			"link" => SOY2PageController::createLink(APPLICATION_ID.".List.Detail.".$entity->getId())
		));
		
		$this->addActionLink("remove_link", array(
			"link" => SOY2PageController::createLink(APPLICATION_ID . ".List.Remove." . $entity->getId()),
			"onclick" => "return confirm('削除してもよろしいですか？');"
		));
		
		if(is_null($entity->getGId())) return false;
	}
}
?>