<?php

class InsertLogic extends SOY2LogicBase{

	/**
	 * @param string, int|string?
	 * @return int
	 */
	function insert(string $filename, $galleryId){
		$image = soygallery_get_image_object(0);
		$image->setFilename($filename);
		$image->setGalleryId($galleryId);
		
		try{
			return SOY2DAOFactory::create("SOYGallery_ImageDAO")->insert($image);
		}catch(Exception $e){
			return 0;
		}		
	}
}