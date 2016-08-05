<?php

class InsertLogic extends SOY2LogicBase{

	private $imageDao;

	function insert($filename,$galleryId){
		if(!$this->imageDao){
			$this->imageDao = SOY2DAOFactory::create("SOYGallery_ImageDAO");
		}
		
		$dao = $this->imageDao;
		
		$image = new SOYGallery_Image();
		
		$image->setFilename($filename);
		$image->setGalleryId($galleryId);
		
		try{
			$id = $dao->insert($image);
		}catch(Exception $e){
			$id = null;
		}
		
		return $id;
	}
}
?>