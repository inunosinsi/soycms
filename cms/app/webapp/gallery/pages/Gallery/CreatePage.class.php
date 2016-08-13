<?php

class CreatePage extends WebPage{

	private $gallery = null;

	function doPost(){
		
		if(soy2_check_token()&&isset($_POST["Gallery"])){
			$dao = SOY2DAOFactory::create("SOYGallery_GalleryDAO");
			
			$gallery = SOY2::cast("SOYGallery_Gallery", $_POST["Gallery"]);
			
			$this->gallery = $gallery;
			
			if($this->checkValidate($gallery)){
				$this->setConfig($gallery);
				try{
					$id = $dao->insert($gallery);
					
					//画像の置き場を生成
					$path = SOY_GALLERY_IMAGE_UPLOAD_DIR . $gallery->getGalleryId();
					if(!is_dir($path)) mkdir($path, 0755);
					
				}catch(Exception $e){
					//
				}
			}
			
			CMSApplication::jump("Gallery.Detail." . $id);
		}
	}
	
	//エラーがあったらfalseを返す
	function checkValidate($gallery){
		
		//ギャラリIDの文字数
		if(strlen($gallery->getGalleryId()) == 0){
			return false;
		}
		
		//ギャラリIDが半角英数字かどうか？
		if(!preg_match("/^[a-zA-Z0-9]+$/", $gallery->getGalleryId())){
			return false;
		}
		
		//ギャラリ名の文字数
		if(strlen($gallery->getName()) == 0){
			return false;
		}
		
		//エラーがあったら、falseを返す
		return true;
	}
	
	//リサイズの設定を入れる
	function setConfig($gallery){
		$config["resize"]["width"] = 640;
		$config["thumbnail"]["width"] = 160;
		$config["count"] = 10;
		$gallery->setConfigArray($config);
		
		return $gallery;
	}

    function __construct() {
    	   	
    	WebPage::__construct();
    	   	
    	$dao = SOY2DAOFactory::create("SOYGallery_GalleryDAO");
    	
    	if(!$this->gallery){
    		$this->gallery = new SOYGallery_Gallery();
    	}
    	
    	$gallery = $this->gallery;
    	
    	$this->addModel("error", array(
    		"visible" => (!is_null($gallery->getGalleryId()))
    	));
    	
    	$this->addForm("form");
    	
    	$this->addInput("name", array(
    		"name" => "Gallery[name]",
    		"value" => $gallery->getName()
    	));
    	$this->addInput("gallery_id", array(
    		"name" => "Gallery[galleryId]",
    		"value" => $gallery->getGalleryId()
    	));
    	$this->addInput("memo", array(
    		"name" => "Gallery[memo]",
    		"value" => $gallery->getMemo()
    	));
    }
}
?>