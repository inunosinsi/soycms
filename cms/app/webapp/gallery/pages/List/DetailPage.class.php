<?php

class DetailPage extends WebPage{
	
	private $id;
	
	function doPost(){
		
		if(soy2_check_token() && isset($_POST["Image"])){
			
			$dao = SOY2DAOFactory::create("SOYGallery_ImageDAO");						
			$image = SOY2::cast(soygallery_get_image_object($this->id), (object)$_POST["Image"]);
			$image->setAttributes(soy2_serialize($_POST["Image"]["attributes"]));
			
			try{
				$dao->update($image);
				CMSApplication::jump("List.Detail." . $this->id . "?updated");
			}catch(Exception $e){
				CMSApplication::jump("List.Detail." . $this->id . "?error");
			}
		}
	}

    function __construct($args) {
		$this->id = (isset($args[0])) ? (int)$args[0] : 0;
		
		parent::__construct();
		
		$image = soygallery_get_image_object($this->id);
		$gallery = soygallery_get_gallery_object($image->getGalleryId());
	
		$cnf = $gallery->getConfigArray();
		$imageDir = (isset($cnf["uploadDir"])) ? $cnf["uploadDir"] : SOY_GALLERY_IMAGE_UPLOAD_DIR . $gallery->getId();
		if(strrpos($imageDir, "/") != strlen($imageDir) - 1) $imageDir .= "/";
		
		$path = "/" . trim(str_replace($_SERVER["DOCUMENT_ROOT"], "", $imageDir), "/") . "/";
		
		$filename = $image->getFilename();
		$thumbnailFilename = "t_" . $filename;
		
		//画像のサイズを取得
		$imageSize = soygallery_get_image_file_info($imageDir . $filename);
		
		//サムネイル画像の取得
		$thumbnailSize = soygallery_get_image_file_info($imageDir . $thumbnailFilename);
		
		foreach(array("updated", "error", "upload") as $idx){
			DisplayPlugin::toggle($idx, isset($_GET[$idx]));
		}
		
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
			"text" => $gallery->getName()
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
			"text" => date("Y/m/d H:i:s", (int)$image->getCreateDate())
		));
		
		$this->addLabel("update_date", array(
			"text" => date("Y/m/d H:i:s", (int)$image->getUpdateDate())
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
			"selected" => ($image->getIsPublic() != SOYGallery_Image::IS_PUBLIC),
			"label" => "非公開"
		));
    }    
}