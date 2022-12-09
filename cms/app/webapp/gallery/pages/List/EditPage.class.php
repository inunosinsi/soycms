<?php

class EditPage extends WebPage{
	
	private $dir = null;
	private $id;
	private $imageDao;
	
	function doPost(){
		
		if(soy2_check_token()){
			
			//ソート順の変更
			if(isset($_POST["update"])){
				
				$sorts = $_POST["Sort"];
				$flg = true;
				foreach($sorts as $key => $value){
					if(!isset($value)) continue;
					//$keyが画像のID
					$imageId = $key;
					
					//$valueが順番
					$sort = mb_convert_kana($value, "a");
					
					//入力した値が数字以外の場合
					if(!is_numeric($sort)) continue;
					
					try{
						$image = $this->imageDao->getById($imageId);
					}catch(Exception $e){
						//オブジェクトを取得できなかった場合はcontinue
						$flg = false;
						continue;
					}
					
					$image->setSort($sort);
					
					try{
						$this->imageDao->update($image);
					}catch(Exception $e){
						$flg = false;
						continue;
					}
				}
				
				if($flg){
					CMSApplication::jump("List.Edit." . $this->id . "?success");
				}else{
					CMSApplication::jump("List.Edit." . $this->id . "?failed");
				}
				
			}
			
			//一斉公開
			if(isset($_POST["public"])){
				$checks = $_POST["Check"];
				if(count($checks) > 0){
					$flg = true;
					
					foreach($checks as $key => $value){
						//$keyがIDになる
						$imageId = $key;
						
						try{
							$image = $this->imageDao->getById($imageId);
						}catch(Exception $e){
							//オブジェクトを取得できなかった場合はcontinue
							$flg = false;
							continue;
						}
					
						$image->setIsPublic(SOYGallery_Image::IS_PUBLIC);
						
						try{
							$this->imageDao->update($image);
						}catch(Exception $e){
							$flg = false;
							continue;
						}
					}
					
					if($flg){
						CMSApplication::jump("List.Edit." . $this->id . "?success");
					}
				}
				CMSApplication::jump("List.Edit." . $this->id . "?failed");
			}
			
			if(isset($_POST["no_public"])){
				$checks = $_POST["Check"];
				if(count($checks) > 0){
					$flg = true;
					
					foreach($checks as $key => $value){
						$image = soygallery_get_image_object($key);	//$keyがIDになる
						$image->setIsPublic(SOYGallery_Image::NO_PUBLIC);
						$image->setSort(99999);
						
						try{
							$this->imageDao->update($image);
						}catch(Exception $e){
							$flg = false;
							continue;
						}
					}
					
					if($flg){
						CMSApplication::jump("List.Edit." . $this->id . "?success");
					}
				}
				CMSApplication::jump("List.Edit." . $this->id . "?failed");
			}
			
			//ファイルのアップロード
			if(isset($_FILES["files"])){
				
				$uploadLogic = SOY2Logic::createInstance("logic.UploadLogic");
				
				//設定値以上の画像サイズの場合
				if(!$uploadLogic->checkUploadSize()) CMSApplication::jump("List.Edit." . $this->id . "?size_error");
				
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
				
				CMSApplication::jump("List.Edit." . $this->id . "?upload");
			}
		}
		CMSApplication::jump("List.Edit." . $this->id . "?size_error");
	}
	
	function __construct($args){
		$this->imageDao = SOY2DAOFactory::create("SOYGallery_ImageDAO");
		$this->id = (isset($args[0])) ? (int)$args[0] : 0;
	
    	parent::__construct();
    	
		foreach(array("error", "updated", "deleted", "deleted_error", "size_error", "success", "failed") as $idx){
			DisplayPlugin::toggle($idx, isset($_GET[$idx]));
		}
		
		$gallery = soygallery_get_gallery_object($this->id);
    	
		$this->addLabel("gallery_name", array(
    		"text" => $gallery->getName()
    	));
    	$this->addLink("config_link", array(
    		"link" => SOY2PageController::createLink(APPLICATION_ID . ".Gallery.Detail." . $gallery->getId())
    	));
			
		$this->addLink("return_link", array(
    		"link" => SOY2PageController::createLink(APPLICATION_ID . ".List." . $gallery->getId())
    	));
    		
		$this->addForm("upload_form", array(
			"attr:id" => "upload_form",
			"attr:enctype" => "multipart/form-data"
		));
		
		$this->addForm("config_form");		
		
		$cnf = $gallery->getConfigArray();
		$imageDir = (isset($cnf["uploadDir"])) ? $cnf["uploadDir"] : SOY_GALLERY_IMAGE_UPLOAD_DIR . $gallery->getGalleryId();
		if(strrpos($imageDir, "/") != strlen($imageDir) - 1) $imageDir .= "/";
		
		$this->createAdd("image_list", "_common.ImageListComponent", array(
			"list" => soygallery_get_image_views_by_gId($this->id),
			"imagePath" => str_replace($_SERVER["DOCUMENT_ROOT"], "", $imageDir)
		));
	}	        
}