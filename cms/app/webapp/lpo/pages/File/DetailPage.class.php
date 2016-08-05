<?php

class DetailPage extends WebPage{
	
	private $file = null;
	
	function doPost(){
		
		if(isset($_POST["do_resize"])){
			$filename = explode(".",basename($this->file));
			$newfile = $this->dir . "s_" . $filename[0] . "." . $filename[1];
			$count = 1;
			
			while(file_exists($newfile)){
				$filename = explode(".",basename($this->file));
				$filename[0] .= "_" . $count;
				$newfile = $this->dir . "s_" . $filename[0] . "." . $filename[1];
				$count++;
			}
			soy2_resizeimage($this->file,$newfile,$_POST["resize"]["width"],$_POST["resize"]["height"]);
			
			$url = SOY2PageController::createLink("Site.File.Detail", true);
			SOY2PageController::redirect($url . "?path=" . str_replace(SOY_LPO_IMAGE_UPLOAD_DIR,"",$newfile));
		}
		
		if(isset($_POST["do_delete"]) && soy2_check_token()){
			@unlink($this->file);
			SOY2PageController::jump("lpo.File?path=" . str_replace(SOY_LPO_IMAGE_UPLOAD_DIR,"",$this->dir));
		}
		
//		if(isset($_POST["go_edit"])){
//			$url = SOY2PageController::createLink("lpo.File.Edit", true);
//			SOY2PageController::redirect($url . "?path=" . str_replace(SOY_LPO_IMAGE_UPLOAD_DIR,"",$this->file));
//		}
	}

    function __construct() {
		if(empty($_GET["path"]))exit;
		$this->file = soy2_realpath(soy2_realpath(SOY_LPO_IMAGE_UPLOAD_DIR) . $_GET["path"]);
		$this->dir = soy2_realpath(dirname($this->file));
		if(!$this->file)exit;
		$parentPath = str_replace(SOY_LPO_IMAGE_UPLOAD_DIR,"",$this->dir);
	
    	WebPage::WebPage();
			
		$this->buildInfo($this->file);
				
		$this->createAdd("move_up_link","HTMLLink",array(
			"link" => ($parentPath) ? SOY2PageController::createLink("lpo.File") . "?path=" . $parentPath
			: SOY2PageController::createLink("lpo.File")
		));
		
		
		$pathinfo = pathinfo($this->file);
		$extension = strtolower($pathinfo["extension"]);
				
		$url = str_replace($_SERVER["DOCUMENT_ROOT"],"http://".$_SERVER["HTTP_HOST"],$this->file);
//		$url = "http://".$_SERVER["HTTP_HOST"]."/lpoImage/".$this->file;
		$this->createAdd("file_url","HTMLLabel",array(
			"text" => $url
		));
		$this->createAdd("file_link","HTMLLink",array(
			"link" => $url
		));
		
		$this->createAdd("filename","HTMLLabel",array(
			"text" => basename($this->file)
		));
		
		$this->createAdd("is_image","HTMLModel",array(
			"visible" => in_array($extension,array("jpg","jpeg","gif","png"))
		));
		
		$this->createAdd("file_image","HTMLImage",array(
			"src" => $url
		));
		
		
		$this->createAdd("form","HTMLForm",array(
			"attr:id" => "form"
		));
		
		$this->createAdd("is_editable","HTMLModel",array(
			"visible" => in_array($extension,array("css","html","js","txt"))
		));
		
    }
    
	
	function buildInfo(){
		
	}
}

if(!function_exists("soy2_resizeimage")){
function soy2_resizeimage($filepath,$savepath,$width = null,$height = null){
	//gd
	//image magick
	if(class_exists("Imagick")){
		$thumb = new Imagick($filepath);
		$imageSize = array($thumb->getImageWidth(),$thumb->getImageHeight());
		//size
		if(is_null($width) && is_null($height)){
			$width = $imageSize[0];
			$height = $imageSize[1];
		}else if(is_null($width)){
			$width = $imageSize[0] * $height / $imageSize[1];
		}else if(is_null($height)){
			$height = $imageSize[1] * $width / $imageSize[0];
		}
		$thumb->thumbnailImage($width,$height);
		$thumb->writeImage($savepath);
		return true;
	}
	//NewMagickWand
	if(function_exists("NewMagickWand")){
		$thumb = NewMagickWand();
		MagickReadImage($thumb,$filepath);
		$imageSize = array(MagickGetImageWidth($thumb),MagickGetImageHeight($thumb));
		//size
		if(is_null($width) && is_null($height)){
			$width = $imageSize[0];
			$height = $imageSize[1];
		}else if(is_null($width)){
			$width = $imageSize[0] * $height / $imageSize[1];
		}else if(is_null($height)){
			$height = $imageSize[1] * $width / $imageSize[0];
		}
		if(!MagickResizeImage($thumb,$width,$height,MW_LanczosFilter,1)){
			trigger_error("Failed [MagickResizeImage] " . __FILE__ . ":" . __LINE__,E_USER_ERROR);
			return -1;
		}
		if(!MagickWriteImage($thumb,$savepath)){
			trigger_error("Failed [MagickWriteImage] " . __FILE__ . ":" . __LINE__,E_USER_ERROR);
			return -1;
		}
	}
	return soy2_image_resizeimage_gd($filepath,$savepath,$width,$height);
}
function soy2_image_resizeimage_gd($filepath,$savepath,$width = null,$height = null){
	//type
	//php version is 5.2.0 use pathinfo($filepath,PATHINFO_EXTENSION);
	$info = pathinfo($filepath); 
	$type = $info["extension"];
	if($type == "jpg")$type = "jpeg";
	$from = "imagecreatefrom" . strtolower($type);
	if(!function_exists($from)){
		trigger_error("Failed [Invalid Type:".$type."] " . __FILE__ . ":" . __LINE__,E_USER_ERROR);
		return -1;
	}
	//source
	$srcImage = $from($filepath);
	//size
	$imageSize = getimagesize($filepath);
	if(is_null($width) && is_null($height)){
		$width = $imageSize[0];
		$height = $imageSize[1];
	}else if(is_null($width)){
		$width = $imageSize[0] * $height / $imageSize[1];
	}else if(is_null($height)){
		$height = $imageSize[1] * $width / $imageSize[0];
	}
	$dstImage = imagecreatetruecolor($width,$height);
	imagecopyresampled($dstImage,$srcImage, 0, 0, 0, 0,
  			$width, $height, $imageSize[0], $imageSize[1]);
  	//php version is 5.2.0 use pathinfo($filepath,PATHINFO_EXTENSION);
  	$info = pathinfo($savepath); 
	$type = $info["extension"];
	switch($type){
		case "jpg":
			return imagejpeg($dstImage,$savepath,100);
			break;
		default:
			$to = "image" . $type;
			if(function_exists($to)){
				$to($dstImage,$savepath);
				return true;
			}
			//invalid type
			trigger_error("Failed [Invalid Type:".$type."] " . __FILE__ . ":" . __LINE__,2);
			return -1;
			break;
	}	
}
}
?>