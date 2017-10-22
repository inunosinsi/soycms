<?php

class DetailPage extends WebPage{

	private $id;
	private $dao;
	private $gallery = null;
	private $errors = false;
	
	const RESIZE_SIZE = 640;
	const THUMBNAIL_SIZE = 160;
	const IMAGE_COUNT = 10;

	function doPost(){
		
		if(soy2_check_token() && isset($_POST["Gallery"])){
			
			$this->dao = SOY2DAOFactory::create("SOYGallery_GalleryDAO");
			$dao = $this->dao;
			
			$oldGallery = $this->getGallery($this->id);
			if(is_null($oldGallery->getId())){
				$oldGallery->setId($this->id);
			}
			
			$gallery = (object)$_POST["Gallery"];
			$gallery = SOY2::cast($oldGallery, $gallery);
			$gallery = $this->setConfig($gallery);
			
			$this->gallery = $gallery;
			
			if($this->checkValidate($gallery)){
				
				try{
					$dao->update($gallery);
					
					//画像の置き場を生成
					$path = SOY_GALLERY_IMAGE_UPLOAD_DIR . $gallery->getGalleryId();
					if(!is_dir($path))mkdir($path, 0755);
					
					CMSApplication::jump("Gallery.Detail." . $this->id);
				}catch(Exception $e){
					//
				}	
			}
		}
		
		$this->errors = true;
	}
	
	//エラーがあったらfalseを返す
	function checkValidate($gallery){
		
		//ギャラリ名の文字数
		if(strlen($gallery->getName()) == 0){
			return false;
		}
		
		//エラーがあったら、falseを返す
		return true;
	}
	
	//リサイズの設定を入れる
	function setConfig($gallery){
		
		$resize_width = mb_convert_kana($_POST["Resize"]["width"], "a");
		$config["resize"]["width"] = (is_numeric($resize_width)) ? $resize_width : self::RESIZE_SIZE;
		
		$resize_height = mb_convert_kana($_POST["Resize"]["height"], "a");
		$config["resize"]["height"] = (is_numeric($resize_height)) ? $resize_height : self::RESIZE_SIZE;
		
		$thumbnail_width = mb_convert_kana($_POST["Thumbnail"]["width"], "a");
		$config["thumbnail"]["width"] = (is_numeric($thumbnail_width)) ? $thumbnail_width : self::THUMBNAIL_SIZE;
		
		$thumbnail_height = mb_convert_kana($_POST["Thumbnail"]["height"], "a");
		$config["thumbnail"]["height"] = (is_numeric($thumbnail_height)) ? $thumbnail_height : self::THUMBNAIL_SIZE;
		
		$count = mb_convert_kana($_POST["Count"], "a");
		$config["count"] = (is_numeric($count)) ? $count : self::IMAGE_COUNT;
		
		$config["uploadDir"] = (isset($_POST["UploadDir"])) ? $_SERVER["DOCUMENT_ROOT"] . $_POST["UploadDir"] : "";
		
		//フォルダがあるか確認
		$uploadDir = str_replace($_SERVER["DOCUMENT_ROOT"], "", $config["uploadDir"]);
		$dirs = explode("/", trim($uploadDir, "/"));
		$root = $_SERVER["DOCUMENT_ROOT"];
		foreach($dirs as $dir){
			$root .= "/" . $dir;
			if(!file_exists($root) || !is_dir($root)){
				mkdir($root);
			}
		}
		
		$gallery->setConfigArray($config);
		
		return $gallery;
	}

    function __construct($args) {
    	
    	$this->id = (isset($args[0])) ? (int)$args[0] : null;
    	
    	parent::__construct();
    	
    	$id = $this->id;
    	
    	if($this->gallery){
    		$gallery = $this->gallery;
    	}else{
    		$gallery = $this->getGallery($id);
    	}
    	
    	$this->addLink("gallery_link", array(
    		"link" => SOY2PageController::createLink(APPLICATION_ID . ".List." . $id)
    	));
    	
    	$this->addModel("error", array(
    		"visible" => ($this->errors)
    	));
    	
    	$this->addForm("form");
    	
    	$this->addLabel("gallery_id", array(
    		"text" => $gallery->getGalleryId()
    	));
    	
    	$this->addLabel("name", array(
    		"name" => "Gallery[name]",
    		"value" => $gallery->getName()
    	));
    	
    	$this->addInput("memo", array(
    		"name" => "Gallery[memo]",
    		"value" => $gallery->getMemo()
    	));
    	
    	$config = $gallery->getConfigArray();
    	
    	$this->addInput("resize_width", array(
    		"name" => "Resize[width]",
    		"value" => (isset($config["resize"]["width"])) ? (int)$config["resize"]["width"] : self::RESIZE_SIZE,
    		"style" => "text-align:right;ime-mode:inactive;"
    	));
    	
    	$this->addInput("resize_height", array(
    		"name" => "Resize[height]",
    		"value" => (isset($config["resize"]["height"])) ? (int)$config["resize"]["height"] : self::RESIZE_SIZE,
    		"style" => "text-align:right;ime-mode:inactive;"
    	));
    	
    	$this->addInput("thumbnail_width", array(
    		"name" => "Thumbnail[width]",
    		"value" => (isset($config["thumbnail"]["width"])) ? (int)$config["thumbnail"]["width"] : self::THUMBNAIL_SIZE,
    		"style" => "text-align:right;ime-mode:inactive;"
    	));
    	
    	$this->addInput("thumbnail_height", array(
    		"name" => "Thumbnail[height]",
    		"value" => (isset($config["thumbnail"]["height"])) ? (int)$config["thumbnail"]["height"] : self::THUMBNAIL_SIZE,
    		"style" => "text-align:right;ime-mode:inactive;"
    	));
    	
    	$this->addInput("count", array(
    		"name" => "Count",
    		"value" => (isset($config["count"])) ? (int)$config["count"] : self::IMAGE_COUNT,
    		"style" => "text-align:right;ime-mode:inactive;"
    	));
    	   
    	$uploadDir = (isset($config["uploadDir"])) ? $config["uploadDir"] : SOY_GALLERY_IMAGE_UPLOAD_DIR . $gallery->getGalleryId();
    	$this->addInput("upload_dir", array(
    		"name" => "UploadDir",
    		"value" => str_replace($_SERVER["DOCUMENT_ROOT"], "", $uploadDir)
    	));
    	
    }
    
    function getGallery($id){
    	if(!$this->dao){
    		$this->dao = SOY2DAOFactory::create("SOYGallery_GalleryDAO");
    	}
    	
    	$dao = $this->dao;
    	
    	try{
    		$gallery = $dao->getById($id);
    	}catch(Exception $e){
    		$gallery = new SOYGallery_Gallery();
    	}
    	
    	return $gallery;
    }
}
?>