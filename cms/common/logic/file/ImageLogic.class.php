<?php

class ImageLogic extends SOY2LogicBase{

	private $force = false;

    function resize($path, $width, $height, $new_path = null, $flag = 0){
    	$info = pathinfo($path);
    	
    	if(strlen($path)<1)return;
    	
    	if(!$new_path)$new_path = $path;
		
		switch(strtolower($info["extension"])){
			case "jpeg":
			case "jpg":
				$img = imagecreatefromjpeg($path);
				$func = "imagejpeg";
				break;
			case "png":
				$img = imagecreatefrompng($path);
				$func = "imagepng";
				break;
			case "gif":
				$img = imagecreatefromgif($path);
				$func = "imagegif";
				break;
			default://対応していない画像はどうしよう
				throw new Exception("Invalid Image");
				exit;
		}
		
		$size = getimagesize($path);
		
		switch($flag){
			//アスペクト維持
			case 1:
				$img_out=imagecreatetruecolor($width,$height);
				
				//真白にする
				imagefill($img_out , 0 , 0 , 0xFFFFFF);
				
				//サイズ計算
				$ratio = ($size[0] > $size[1]) ? $width / $size[0] : $height / $size[1];
				
				$new_width = $size[0] * $ratio;
				$new_height = $size[1] * $ratio;
				
				$x = ($width - $new_width) / 2;
				$y = ($height - $new_height) / 2;
				
				ImageCopyResampled($img_out,$img,$x,$y,0,0,$new_width,$new_height,$size[0],$size[1]);
			
				break;
			
			//トリミング
			case 2:
				$img_out=imagecreatetruecolor($width,$height);
				
				$x = ($size[0] - $width) / 2;
				$y = ($size[1] - $height) / 2;
				
				ImageCopyResampled($img_out,$img,0,0,$x,$y,$width,$height,$width,$height);
				
				break;
			
			//リサイズ
			case 0:
			default:

				if(is_null($width) && is_null($height)){
					$width = $size[0];
					$height = $size[1];
				}else if(is_null($width)){
					$width = (int)($size[0] * $height / $size[1]);
				}else if(is_null($height)){
					$height = (int)($size[1] * $width / $size[0]);
				}
				
				$img_out=imagecreatetruecolor($width,$height);
				
				ImageCopyResampled($img_out,$img,0,0,0,0,$width,$height,$size[0],$size[1]);
		}
		
		if($func == "imagejpeg"){
			imagejpeg($img_out,$new_path,100);	//jpegの画質
		}else{
			$func($img_out,$new_path);
		}
    }
    
    /**
     * @return new_filepath
     */
    function makeThumbnail($path,$width,$height,$flag = false){
    	
    	$name = basename($path);
    	
    	if($flag){
			$new_path = preg_replace('/'.$name.'$/',"thumbnail_".$width."x".$height.'_'.$name,$path);
			$flag = 1;
    	}else{
    		$new_path = preg_replace('/'.$name.'$/',"thumbnail_".$width."_".$height.'_'.$name,$path);
    		$flag = 0;
    	}
    	
    	if($this->getForce() || !file_exists($new_path)){
    		$this->resize($path,$width,$height,$new_path,$flag);
    	}
    	
    	return $new_path;
    }
    
    /**
     * @return new_filepath
     */
    function trimImage($path,$width,$height,$type = "middle"){
    	$name = basename($path);
    	$new_path = preg_replace('/'.$name.'$/',"thumbnail_".$width."x".$height.'_'.$name,$path);
    	   
    	if($this->getForce() || !file_exists($new_path)){
    		$this->resize($path,$width,$height,$new_path,2);
    	}
    	
    	return $new_path;
    }

    function getForce() {
    	return $this->force;
    }
    function setForce($force) {
    	$this->force = $force;
    }
}
?>