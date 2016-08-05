<?php

class UploadLogic extends SOY2LogicBase{

	//リサイズした時のファイル名とサムネイルのファイル名を返す
	function uploadFile($file,$tmp,$category){
		
		$configDao = SOY2DAOFactory::create("SOYList_ConfigDAO");
		$configObj = $configDao->get();
		$config = $configObj->getConfigArray();
		
		$resize_x = (isset($config["resize"]["width"]) && (int)$config["resize"]["width"] > 0) ? (int)$config["resize"]["width"] : null;
		$resize_y = (isset($config["resize"]["height"]) && (int)$config["resize"]["height"] > 0) ? (int)$config["resize"]["height"] : null;
		
		$file = $this->getUniqueFileName($file);
		$path = SOY_LIST_IMAGE_UPLOAD_DIR . $file;
		
		move_uploaded_file($tmp,$path);
		
		//リサイズを行うか？チェックする
		list($res,$mode) = $this->checkSizeBeforeResize(getimagesize($path),$resize_x,$resize_y);
		
		//リサイズをする必要があるかどうか？
		if($res===true){
			$resized_path = $path;
//			unlink($path);
			if($mode==="width"){
				$resize_y = null;
			}elseif($mode==="height"){
				$resize_x = null;
			}
			soy2_resizeimage($path,$resized_path,$resize_x,$resize_y);
		}
				
		return $file;
	}
	
	function checkSizeBeforeResize($image,$resize_x,$resize_y){
		$res = true;
		$width = $image[0];
		$height = $image[1];
		
		//縦横比を調べる
		if($width > $height){
			$mode = "width";
			$res = ($width > $resize_x) ? true : false;
		//縦長画像
		}else{
			$mode = "height";
			$res = ($height > $resize_y) ? true : false;
		}
				
		return array($res,$mode);
	}
	
	function getUniqueFileName($file){
		$fileType = substr($file,strrpos($file,"."));
		return rand(10000,90000)."_".rand(10000,90000)."_".rand(10000,90000).$fileType;	
	}
	
	function deleteFile($file){
		$path = SOY_LIST_IMAGE_UPLOAD_DIR . $file;	
		unlink($path);
	}
}
?>