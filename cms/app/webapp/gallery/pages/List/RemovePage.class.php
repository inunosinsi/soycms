<?php

class RemovePage extends WebPage{

	private $galleryId;

    function __construct($args) {
    	
    	if(soy2_check_token()){
    		$id = (isset($args[0])) ? $args[0] : null;
	
	    	$dao = SOY2DAOFactory::create("SOYGallery_ImageDAO");
	    	
	    	try{
	    		$image = $dao->getById($id);
	    	}catch(Exception $e){
	    		//画像が取得できなかった場合はギャラリ一覧に戻る
	    		CMSApplication::jump("Gallery");
	    	}
	    	
	    	//画像削除用のパスを作成する
	    	$this->galleryId = $image->getGalleryId();
	    	$galleryName = $this->getGalleryName($this->galleryId);
	    	if(isset($galleryName)){
	    		$imageDir = SOY_GALLERY_IMAGE_UPLOAD_DIR . $galleryName . "/";
	    	}else{
	    		//画像のパスが作成できなかった場合はギャラリ一覧に戻る
	    		CMSApplication::jump("List");
	    	}
	    	
	    	//削除
	    	$this->deleteImage($imageDir, $image->getFilename());
	    	
	    	try{
	    		$dao->deleteById($id);
	    	}catch(Exception $e){
	    		
	    	}
	
	   		CMSApplication::jump("List.Edit." . $this->galleryId . "?deleted");
    	}
    }
    
    function deleteImage($imageDir, $filename){
    	$thumbnailFilename = "t_" . $filename;
    	
  		unlink($imageDir . $filename);
    	unlink($imageDir . $thumbnailFilename);    	
    	
    }
    
    function getGalleryName($id){
    	$dao = SOY2DAOFactory::create("SOYGallery_GalleryDAO");
    	try{
    		$gallery = $dao->getById($id);
    	}catch(Exception $e){
    		$gallery = new SOYGallery_Gallery();
    	}
    	
    	return $gallery->getGalleryId();
    }
}
?>