<?php

class UploadLogic extends SOY2LogicBase{

	/**
	 * @アップロードされた画像のサイズを調べる
	 * @return boolean エラーがあった場合はfalseを返す
	 */
	function checkUploadSize(){
		$errors = $_FILES["files"]["error"];
		
		$hasError = false;
		
		foreach($errors as $error){
			if($error === UPLOAD_ERR_INI_SIZE){
				$hasError = true;
				break;
			}
		}

		return (!$hasError);
	}

	//multipleでアップロードしたファイルの整理
	function organizeFiles($values){
		$files = array();
		
		$names = $values["name"];
		$types = $values["type"];
		$tmps = $values["tmp_name"];
		$errors = $values["error"];
		$sizes = $values["size"];
		
		
		for($i = 0; $i < count($errors); $i++){
			//エラーの場合は値を保持しない
			if($errors[$i] === 1){
				/**
				 * @エラーしたファイルのログを残したい
				 */
				continue;
			}
			
			$file = array();
			
			$file["name"] = $names[$i];
			$file["type"] = $types[$i];
			$file["tmp_name"] = $tmps[$i];
			$file["error"] = 0;
			$file["size"] = $sizes[$i];
			
			$files[] = $file;
		}
		
		return $files;
	}

	//リサイズした時のファイル名とサムネイルのファイル名を返す
	function uploadFile($file, $tmp, $galleryId){
		
		$gallery = $this->getGallery($galleryId);
		$config = $gallery->getConfigArray();
		$uploadDir = (isset($config["uploadDir"])) ? $config["uploadDir"] : SOY_GALLERY_IMAGE_UPLOAD_DIR . $gallery->getGalleryId();
		
		$file = $this->getUniqueFileName($file);
		$path = rtrim($uploadDir , "/") . "/" . $file;
		
		$result = move_uploaded_file($tmp, $path);
		@chmod($path,0604);	
		if($result){
			$resize_width = (isset($config["resize"]["width"]) && (int)$config["resize"]["width"] > 0) ? (int)$config["resize"]["width"] : 3000;
			$resize_height = (isset($config["resize"]["height"]) && (int)$config["resize"]["height"] > 0) ? (int)$config["resize"]["height"] : 3000;
			
			$res = $this->checkSizeBeforeResize(getimagesize($path), $resize_width, $resize_height);
			
			//リサイズをする必要があるかどうか？
			if($res["exe"] === true){
				$resized_path = $path;
				if($res["mode"] === "width"){
					$width = $resize_width;
					$height = null;
				}else{
					$width = null;
					$height = $resize_height;
				}
				
				soy2_resizeimage($path, $resized_path, $width, $height);
				$path = $resized_path;
			}
			
			//ここからサムネイルを作成
			$thumbnail_file = "t_" . $file;
			$thumbnail_path = rtrim($uploadDir , "/") . "/" . $thumbnail_file;
			$thumbnail_width = (isset($config["thumbnail"]["width"]) && (int)$config["thumbnail"]["width"] && $res["mode"] === "width") ? (int)$config["thumbnail"]["width"] : null;
			$thumbnail_height = (isset($config["thumbnail"]["height"]) && (int)$config["thumbnail"]["height"] && $res["mode"] === "height") ? (int)$config["thumbnail"]["height"] : null;
			soy2_resizeimage($path, $thumbnail_path, $thumbnail_width, $thumbnail_height);
			
			return array($file, $thumbnail_file);
		}
		
		return array();
	}
	
	function checkSizeBeforeResize($image, $resize_width, $resize_height){
		$res["exe"] = false;
		$res["mode"] = null;
		$width = $image[0];
		$height = $image[1];
		
		//横長の画像の場合
		if($width - $height > 0){
			$res["mode"] = "width";
			if($width - $resize_width > 0) $res["exe"] = true;
		//縦長の画像の場合
		}else{
			$res["mode"] = "height";
			if($height - $resize_height > 0) $res["exe"] = true;
		}
		
		return $res;
	}
	
	function getUniqueFileName($file){
		$fileType = substr($file,strrpos($file,"."));
		return rand(10000,90000)."_".rand(10000,90000)."_".rand(10000,90000).$fileType;	
	}
	
	private $galleryDao;
	
	function getGallery($id){
		if(!$this->galleryDao){
			$this->galleryDao = SOY2DAOFactory::create("SOYGallery_GalleryDAO");
		}
    	
    	$dao = $this->galleryDao;
    	
    	try{
    		$gallery = $dao->getById($id);
    	}catch(Exception $e){
    		$gallery = new SOYGallery_Gallery();
    	}
    	
    	return $gallery;
    }
}
?>