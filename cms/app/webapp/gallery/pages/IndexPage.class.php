<?php

class IndexPage extends WebPage{

	function __construct() {
		parent::__construct();

		$galleries = $this->getGalleries();
		$this->addModel("no_gallery_list", array(
			"visible" => (count($galleries) === 0)
		));

		$this->createAdd("gallery_list", "_common.GalleryListComponent", array(
			"list" => $galleries
		));

		$images = $this->getImages();

		$this->addModel("no_image_list", array(
			"visible" => (count($images) === 0)
		));

		$this->createAdd("image_list", "_common.NewImageListComponent", array(
			"list" => $images
		));
	}

	function getGalleries(){
		$limit = 5;

		$dao = SOY2DAOFactory::create("SOYGallery_GalleryDAO");
		$dao->setLimit($limit);
		$dao->setOrder("create_date DESC");
		try{
			$galleries = $dao->get();
		}catch(Exception $e){
			$galleries = array();
		}
		return $galleries;
	}

	function getImages(){
		$limit = 15;

		$dao = SOY2DAOFactory::create("SOYGallery_ImageViewDAO");
		$dao->setLimit($limit);
		$dao->setOrder("create_date DESC");
		try{
			$images = $dao->get();
		}catch(Exception $e){
			$images = array();
		}
		return $images;
	}
}
