<?php

class UploadLogic extends SOY2LogicBase{

	function uploadFile($file,$tmp){
		$new = $this->getUniqueFileName($file);
		$path = SOY_VOICE_IMAGE_UPLOAD_DIR . $new;
		
		move_uploaded_file($tmp,$path);
		
		$res = $this->checkImageSize(getimagesize($path));
		
		if($res){
			$config = $this->getConfig();
			if($config->getIsResize()==1){
				$res = $this->checkSizeBeforeResize(getimagesize($path),$config->getResize());
				if($res){
					$resize_new = "r_".$new;
					$resized_path = SOY_VOICE_IMAGE_UPLOAD_DIR . $resize_new;
					if(soy2_resizeimage($path,$resized_path,$config->getResize())){
						unlink($path);
						$new = $resize_new;
					}
				}
			}
		}else{
			unlink($path);
			$new = null;
		}	
		
		return $new;
	}
	function checkSizeBeforeResize($image,$resize_width){
		$res = true;
		$width = $image[0];
		if($resize_width-$width>0){
			$res = false;
		}
		
		return $res;
	}
	
	function checkImageSize($image){
		$res = true;
		$width = $image[0];
		$height = $image[1];
		if($width/$height>4||$height/$width>4){
			$res = false;
		}
		
		return $res;
		
	}
	
	function getUniqueFileName($file){
		$fileType = substr($file,strrpos($file,"."));
		return rand(10000,90000)."_".rand(10000,90000)."_".rand(10000,90000).$fileType;	
	}
	
	function getConfig(){
    	
    	$dao = SOY2DAOFactory::create("SOYVoice_ConfigDAO");
    	try{
    		$config = $dao->getById(1);
    	}catch(Exception $e){
    		$config = new SOYVoice_Config();
    	}
    	
    	return $config;
    }
    
    function checkValidate($value){
		
		$res = true;
		
		//お名前が空の時
		if(strlen($value["nickname"])==0){
			$res = false;
		}
		
		//コメントが空の時
		if(strlen($value["content"])==0){
			$res = false;
		}
		
		//URLチェック
		if(isset($value["url"])&&strlen($value["url"])>0){
			if(!preg_match('/^(https?|ftp)(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)$/',$value["url"])){
				$res = false;
			}
		}
		
		//メールアドレスチェック
		if(isset($value["email"])&&strlen($value["url"])>0){
			if(!preg_match('/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/',$value["email"])){
				$res = false;
			}
		}
		
		$date = $value["date"];
		
		foreach($date as $key => $value){
			
			if($key=="year"){
				if(strlen($value)!=4)$res = false;
			}else{
				if(strlen($value)>2)$res = false;
			}
		}	
		return $res;
	}

}
?>