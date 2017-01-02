<?php

class DetailPage extends WebPage{
	
	private $id;
	private $gallery;
	
	function doPost(){
		
		if(soy2_check_token() && isset($_POST["Image"])){
			
			$dao = SOY2DAOFactory::create("SOYGallery_ImageDAO");
			
			$id = $this->id;
			$image = $this->getImage($id);
			$attributes = soy2_serialize($_POST["Image"]["attributes"]);
						
			$image = SOY2::cast($image, (object)$_POST["Image"]);
			$image->setAttributes($attributes);
			
			try{
				$dao->update($image);
				CMSApplication::jump("List.Detail." . $id . "?updated");
			}catch(Exception $e){
				CMSApplication::jump("List.Detail." . $id . "?error");
			}
			
		}
		
	}

    function __construct($args) {
		$this->id = (isset($args[0])) ? (int)$args[0] : null;
		
		WebPage::__construct();
				
		$image = $this->getImage($this->id);
		
		$galleryId = $this->getGalleryId($image->getGalleryId());
		$config = $this->gallery->getConfigArray();
		$imageDir = (isset($config["uploadDir"])) ? $config["uploadDir"] : SOY_GALLERY_IMAGE_UPLOAD_DIR . $galleryId;
		
		$path = "/" . trim(str_replace($_SERVER["DOCUMENT_ROOT"], "", $imageDir), "/") . "/";
		
		$filename = $image->getFilename();
		$thumbnailFilename = "t_" . $filename;
		
		//画像のサイズを取得
		$imageSize = $this->getImageFileInfo($imageDir . $filename);
		
		//サムネイル画像の取得
		$thumbnailSize = $this->getImageFileInfo($imageDir . $thumbnailFilename);
		
		$this->addModel("updated", array(
			"visible" => (isset($_GET["updated"]))
		));
		
		$this->addModel("error", array(
			"visible" => (isset($_GET["error"]))
		));
		
		$this->addModel("upload", array(
			"visible" => (isset($_GET["upload"]))
		));
		
		$this->addLink("return_link", array(
			"link" => SOY2PageController::createLink(APPLICATION_ID . ".List.Edit." . $image->getGalleryId())
		));
		
		$this->addForm("form");
		
		$this->addLabel("id", array(
			"text" => $image->getId()
		));
		
		$this->addLink("image_link", array(
			"link" => $path . $filename
		));
		
		$width = ($imageSize[0] > 400) ? 400 : $imageSize[0];
		
		$this->addImage("image", array(
			"src" => $path . $filename,
			"style" => "width:" . $width . "px;"
		));
		
		$this->addLink("gallery_link", array(
			"link" => SOY2PageController::createLink(APPLICATION_ID . ".List." . $image->getGalleryId()),
			"text" => $this->gallery->getName()
		));
		
		$attributes = $image->getAttributeArray();
		$this->addInput("alt", array(
			"name" => "Image[attributes][alt]",
			"value" => (isset($attributes["alt"])) ? $attributes["alt"] : ""
		));
		
		$this->addInput("url", array(
			"name" => "Image[url]",
			"value" => $image->getUrl(),
			"style" => "width:95%;ime-mode:inactive;"
		));
		
		$this->addLabel("image_size", array(
			"text" => $imageSize[0] . "px x " . $imageSize[1] . "px"
		));
		
		$this->addLabel("thumbnail_size", array(
			"text" => $thumbnailSize[0] . "px x " . $thumbnailSize[1] . "px"
		));
		
		$this->addTextArea("memo", array(
			"name" => "Image[memo]",
			"value" => $image->getMemo()
		));
		
		$this->addLabel("create_date", array(
			"text" => date("Y/m/d H:i:s", $image->getCreateDate())
		));
		
		$this->addLabel("update_date", array(
			"text" => date("Y/m/d H:i:s", $image->getUpdateDate())
		));
		
		$this->addCheckBox("is_public", array(
			"name" => "Image[isPublic]",
			"value" => 1,
			"selected" => ($image->getIsPublic() == SOYGallery_Image::IS_PUBLIC),
			"label" => "公開"
		));
		$this->addCheckBox("no_public",array(
			"name" => "Image[isPublic]",
			"value" => 0,
			"selected" => ($image->getIsPublic()!=1),
			"label" => "非公開"
		));
		
    }
    
    function getImageFileInfo($path){
    	$info = getimagesize($path);
    	return array($info[0],$info[1]);
    }
    
    function getImage($id){
    	$dao = SOY2DAOFactory::create("SOYGallery_ImageDAO");
    	
    	try{
    		$image = $dao->getById($id);
    	}catch(Exception $e){
    		$image = new SOYGallery_Image();
    	}
    	
    	return $image;
    }
    
    function getGalleryId($id){
    	$dao = SOY2DAOFactory::create("SOYGallery_GalleryDAO");
    	try{
    		$gallery = $dao->getById($id);
    	}catch(Exception $e){
    		$gallery = new SOYGallery_Gallery();
    	}
    	
    	$this->gallery = $gallery;
    	
    	return $gallery->getGalleryId();
    }
}
?>