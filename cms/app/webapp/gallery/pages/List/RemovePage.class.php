<?php

class RemovePage extends WebPage{

	private $galleryId;

    function __construct($args) {
    	
    	if(soy2_check_token()){
    		$id = (isset($args[0])) ? $args[0] : 0;
		    	
			$image = soygallery_get_image_object($id);
			if(!is_numeric($image->getId())) CMSApplication::jump("Gallery");	//画像が取得できなかった場合はギャラリ一覧に戻る
	    	
	    	//画像削除用のパスを作成する
	    	$galleryName = soygallery_get_gallery_object($image->getGalleryId())->getGalleryId();
			if(!isset($galleryName)) CMSApplication::jump("List");	//画像のパスが作成できなかった場合はギャラリ一覧に戻る
	    			    	
	    	//削除
	    	self::_deleteImage(SOY_GALLERY_IMAGE_UPLOAD_DIR . $galleryName . "/", $image->getFilename());
	    	
	    	try{
	    		SOY2DAOFactory::create("SOYGallery_ImageDAO")->deleteById($id);
	    	}catch(Exception $e){
	    		
	    	}
	
	   		CMSApplication::jump("List.Edit." . $image->getGalleryId() . "?deleted");
    	}
    }
    
    private function _deleteImage(string $imageDir, string $filename){
  		unlink($imageDir . $filename);
    	unlink($imageDir . "t_" . $filename); //サムネイルの方   	
    	
    }    
}
