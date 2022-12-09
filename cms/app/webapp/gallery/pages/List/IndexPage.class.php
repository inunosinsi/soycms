<?php

class IndexPage extends WebPage{
	
	private $id;
	private $imageDao;
	
	function doPost(){
		
		//ファイルのアップロードの方
		if(soy2_check_token() && isset($_FILES["files"])){
			$uploadLogic = SOY2Logic::createInstance("logic.UploadLogic");
			
			//設定値以上の画像サイズの場合
			if(!$uploadLogic->checkUploadSize()) CMSApplication::jump("List." . $this->id . "?size_error");
			
			$insertLogic = SOY2Logic::createInstance("logic.InsertLogic");
			
			//一括アップロードに対応
			$files = $uploadLogic->organizeFiles($_FILES["files"]);
			
			foreach($files as $file){
				$name = $file["name"];
				$tmpname = $file["tmp_name"];
				
				if(isset($name) && preg_match('/(jpg|jpeg|gif|png)$/i', $name)){
					
					list($fileName, $thumbnailFileName) = $uploadLogic->uploadFile($name, $tmpname, $this->id);
											
					//リサイズ画像の登録
					$insertLogic->insert($fileName, $this->id);
				}
			}
			
			CMSApplication::jump("List." . $this->id . "?upload");
		}
		
		//非同期通信の方
		if(isset($_POST["sort"])){
			$sorts = explode(",", $_POST["sort"]);
			
			foreach($sorts as $key => $sort){				
				$int = $key + 1;	//順番
				$id = $sort;		//画像のID
				
				$this->update($id, $int);
			}
		}
		
		if(isset($_POST["public"])){
			$this->updateNoPublic((int)$_POST["public"]);
		}
	}
	
	function __construct($args){
		
		$this->id = (isset($args[0])) ? (int)$args[0] : 0;
		
		parent::__construct();

		foreach(array("updated", "size_error") as $idx){
			DisplayPlugin::toggle($idx, isset($_GET[$idx]));
		}
				
		$gallery = soygallery_get_gallery_object($this->id);
    	$this->addLabel("gallery_name", array(
    		"text" => $gallery->getName()
    	));
		
		$this->addForm("upload_form", array(
			"id" => "uploadsubmit",
			"attr:enctype" => "multipart/form-data"
		));
		
		$this->addForm("form", array(
			"id" => "onsubmit"
		));
		
		$this->addLink("edit_link", array(
			"link" => SOY2PageController::createLink(APPLICATION_ID . ".List.Edit." . $this->id)
		));
		
		$config = $gallery->getConfigArray();
		$imageDir = (isset($config["uploadDir"])) ? $config["uploadDir"] : SOY_GALLERY_IMAGE_UPLOAD_DIR . $gallery->getGalleryId();
		if(strrpos($imageDir, "/") != strlen($imageDir) - 1) $imageDir .= "/";
		
		SOY2::import("domain.SOYGallery_Image");
		$this->createAdd("image_list", "_common.ImageListComponent", array(
			"list" => $this->getImages($this->id),
			"propaty" => "column_",
			"imagePath" => str_replace($_SERVER["DOCUMENT_ROOT"], "", $imageDir)
		));
		
		
		$this->addLabel("script", array(
			"html" => "<script type=\"text/javascript\" src=\"" . CMSApplication::getRoot(). "webapp/" . APPLICATION_ID . "/js/dragdrop.js" . "\"></script>"
		));	
	}
	
	/**
	 * @param int
	 * @return array();
	 */
	function getImages($id){
		$gallery = soygallery_get_gallery_object($id);
		if(is_null($gallery->getGalleryId())) return array();
		
		try{
			return SOY2DAOFactory::create("SOYGallery_ImageViewDAO")->getByGalleryIdAndIsPublic($gallery->getGalleryId());
		}catch(Exception $e){
			return array();
		}
	}
	
	/**
	 * @param int, int
	 * @return bool
	 */
	function update($imageId, $int){
		$image = soygallery_get_image_object($imageId);
		if(!is_numeric($image->getId())) return false;
		
		$image->setSort($int);
		
		try{
			SOY2DAOFactory::create("SOYGallery_ImageDAO")->update($image);
		}catch(Exception $e){
			return false;
		}
		
		return true;
	}
	
	/**
	 * @param int
	 * @return int
	 */
	function updateNoPublic(int $imageId){
		$image = soygallery_get_image_object($imageId);
		if(!is_numeric($image->getId())) return false;
		
		$image->setSort(99999);
		$image->setIsPublic(SOYGallery_Image::NO_PUBLIC);
		
		try{
			SOY2DAOFactory::create("SOYGallery_ImageDAO")->update($image);
		}catch(Exception $e){
			return false;
		}

		return true;
	}
}
