<?php

class DetailPage extends WebPage{

	private $id;
	
	const RESIZE_SIZE = 640;
	const THUMBNAIL_SIZE = 160;
	const IMAGE_COUNT = 10;

	function doPost(){
		
		if(soy2_check_token() && isset($_POST["Gallery"])){
									
			$gallery = SOY2::cast(soygallery_get_gallery_object($this->id), (object)$_POST["Gallery"]);

			$resize = $_POST["Resize"];
			$thumbnail = $_POST["Thumbnail"];
			soygallery_set_gallery_config($gallery, (int)$resize["width"], (int)$resize["height"], (int)$thumbnail["width"], (int)$thumbnail["height"], (int)$_POST["Count"], $_POST["UploadDir"]);
				
			try{
				SOY2DAOFactory::create("SOYGallery_GalleryDAO")->update($gallery);
			}catch(Exception $e){
				CMSApplication::jump("Gallery.Detail." . $this->id . "?error");
			}

			//画像の置き場を生成
			$path = SOY_GALLERY_IMAGE_UPLOAD_DIR . $gallery->getGalleryId();
			if(!is_dir($path))mkdir($path, 0755);
			
			CMSApplication::jump("Gallery.Detail." . $this->id . "?updated");
		}		
	}
	
    function __construct($args) {
    	$this->id = (isset($args[0])) ? (int)$args[0] : 0;
    	
    	parent::__construct();
    	
		$gallery = soygallery_get_gallery_object($this->id);
    	
    	$this->addLink("gallery_link", array(
    		"link" => SOY2PageController::createLink(APPLICATION_ID . ".List." . $gallery->getId())
    	));

		foreach(array("error", "updated") as $idx){
			DisplayPlugin::toggle($idx, isset($_GET[$idx]));
		}
    	    	
    	$this->addForm("form");
    	
    	$this->addLabel("gallery_id", array(
    		"text" => $gallery->getGalleryId()
    	));
    	
    	$this->addInput("name", array(
    		"name" => "Gallery[name]",
    		"value" => $gallery->getName(),
			"attr:required" => "required"
    	));
    	
    	$this->addInput("memo", array(
    		"name" => "Gallery[memo]",
    		"value" => $gallery->getMemo()
    	));
    	
    	$config = $gallery->getConfigArray();
    	
    	$this->addInput("resize_width", array(
    		"name" => "Resize[width]",
    		"value" => (isset($config["resize"]["width"])) ? (int)$config["resize"]["width"] : self::RESIZE_SIZE,
			"attr:required" => "required"
    	));
    	
    	$this->addInput("resize_height", array(
    		"name" => "Resize[height]",
    		"value" => (isset($config["resize"]["height"])) ? (int)$config["resize"]["height"] : self::RESIZE_SIZE,
			"attr:required" => "required"
    	));
    	
    	$this->addInput("thumbnail_width", array(
    		"name" => "Thumbnail[width]",
    		"value" => (isset($config["thumbnail"]["width"])) ? (int)$config["thumbnail"]["width"] : self::THUMBNAIL_SIZE,
			"attr:required" => "required"
    	));
    	
    	$this->addInput("thumbnail_height", array(
    		"name" => "Thumbnail[height]",
    		"value" => (isset($config["thumbnail"]["height"])) ? (int)$config["thumbnail"]["height"] : self::THUMBNAIL_SIZE,
			"attr:required" => "required"
    	));
    	
    	$this->addInput("count", array(
    		"name" => "Count",
    		"value" => (isset($config["count"])) ? (int)$config["count"] : self::IMAGE_COUNT,
			"attr:required" => "required"
    	));
    	   
    	$uploadDir = (isset($config["uploadDir"])) ? $config["uploadDir"] : SOY_GALLERY_IMAGE_UPLOAD_DIR . $gallery->getGalleryId();
    	$this->addInput("upload_dir", array(
    		"name" => "UploadDir",
    		"value" => str_replace($_SERVER["DOCUMENT_ROOT"], "", $uploadDir)
    	));   	
    }    
}