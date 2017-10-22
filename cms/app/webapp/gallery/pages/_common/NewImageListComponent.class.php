<?php
class NewImageListComponent extends HTMLList{

	protected function populateItem($entity){

		$imageDir = $entity->getConfigValue("uploadDir");
		if(!isset($imageDir) || !strlen($imageDir)) $imageDir = self::getUploadDirByGalleryId($entity->getGalleryId());
		if(!isset($imageDir) || !strlen($imageDir)) $imageDir = SOY_GALLERY_IMAGE_UPLOAD_DIR . $entity->getGalleryId();

		$imagePath = "/" . trim(str_replace($_SERVER["DOCUMENT_ROOT"], "", $imageDir), "/") . "/";

		$this->addImage("image", array(
			"src" => $imagePath . "t_".$entity->getFilename()
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

	private function getUploadDirByGalleryId($galleryId){
		static $dao, $list;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("SOYGallery_GalleryDAO");

		if(!isset($galleryId)) return "";
		if(isset($list[$galleryId])) return $list[$galleryId];

		try{
			$gallery = $dao->getByGalleryId($galleryId);
		}catch(Exception $e){
			$gallery = new SOYGallery_Gallery();
		}

		$uploadDir = $gallery->getConfigValue("uploadDir");
		$list[$galleryId] = $uploadDir;

		return $list[$galleryId];
	}
}
