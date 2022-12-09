<?php

class CreatePage extends WebPage{

	function doPost(){

		if(soy2_check_token() && isset($_POST["Gallery"])){
			$dao = SOY2DAOFactory::create("SOYGallery_GalleryDAO");
			$gallery = SOY2::cast("SOYGallery_Gallery", $_POST["Gallery"]);

			$gallery = soygallery_set_gallery_config($gallery);
			try{
				$id = $dao->insert($gallery);
			}catch(Exception $e){
				CMSApplication::jump("Gallery.Create?error");	
			}

			//画像の置き場を生成
			$path = SOY_GALLERY_IMAGE_UPLOAD_DIR . $gallery->getGalleryId();
			if(!is_dir($path)) mkdir($path, 0755);
			
			CMSApplication::jump("Gallery.Detail." . $id);
		}
	}

    function __construct() {

    	parent::__construct();
   	
    	$gallery = soygallery_get_gallery_object(0);

		DisplayPlugin::toggle("error", isset($_GET["error"]));

    	$this->addForm("form");

    	$this->addInput("name", array(
    		"name" => "Gallery[name]",
    		"value" => $gallery->getName(),
			"attr:required" => "required"
    	));
    	$this->addInput("gallery_id", array(
    		"name" => "Gallery[galleryId]",
    		"value" => $gallery->getGalleryId(),
			"attr:required" => "required",
			"attr:pattern" => "^[0-9A-Za-z]+$"
    	));
    	$this->addInput("memo", array(
    		"name" => "Gallery[memo]",
    		"value" => $gallery->getMemo()
    	));
    }
}