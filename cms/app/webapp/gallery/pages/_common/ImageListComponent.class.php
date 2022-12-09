<?php

class ImageListComponent extends HTMLList{
	
	private $propaty;
	private $imagePath;
	
	protected function populateItem($entity){
		
		$this->addCheckBox("public_check", array(
			"name" => "Check[" . $entity->getId() . "]",
			"value" => SOYGallery_Image::IS_PUBLIC
		));

		$this->addImage("image", array(
			"src" => "/" . trim($this->imagePath, "/") . "/" . "t_" . $entity->getFilename(),
			"id" => (isset($this->propaty)) ? $this->propaty . $entity->getId() : ""
		));
		
		$this->addLabel("memo", array(
			"html" => nl2br($entity->getMemo())
		));
		
		$this->addLabel("is_public", array(
			"html" => ($entity->getIsPublic() == SOYGallery_Image::IS_PUBLIC) ? "<span style=\"font-size:1.2em;font-weight:bold;\">公開</span>" : "非公開"
		));
		
		$this->addLabel("update_date", array(
			"text" => date("Y/m/d H:i:s", (int)$entity->getUpdateDate())
		));
		
		$this->addInput("sort", array(
			"name" => "Sort[" . $entity->getId() . "]",
			"value" => (!is_null($entity->getSort()) && $entity->getSort()<99999) ? (int)$entity->getSort() : null,
			"style" => "text-align:right;ime-mode:inactive;width:80%;"
		));
		
		$this->addLink("detail_link", array(
			"link" => SOY2PageController::createLink(APPLICATION_ID . ".List.Detail." . $entity->getId())
		));
		
		$this->addActionLink("remove_link", array(
			"link" => SOY2PageController::createLink(APPLICATION_ID . ".List.Remove." . $entity->getId()),
			"onclick" => "return confirm('削除してもよろしいですか？');"
		));
	}
	
	function setPropaty($propaty){
		$this->propaty = $propaty;
	}
	
	function setImagePath($imagePath){
		$this->imagePath = $imagePath;
	}
}