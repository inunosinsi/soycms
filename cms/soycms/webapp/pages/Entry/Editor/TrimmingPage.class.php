<?php

class TrimmingPage extends CMSWebPageBase {

	private $responseObject;

	function doPost(){
		
		if(soy2_check_token()){
			$responseObject = new StdClass();
			$responseObject->result = false;
			$responseObject->imagePath = null;
			
			//Siteのアップロードディレクトリを調べる
			$action = SOY2ActionFactory::createInstance("SiteConfig.DetailAction");
			$result = $action->run();
			$entity = $result->getAttribute("entity");
			
			$uploadFileDir = str_replace("//" , "/" , UserInfoUtil::getSiteDirectory() . $entity->getDefaultUploadDirectory()) . "/";
			$uploadThumbDir = $uploadFileDir . "thumb";		
			if(!file_exists($uploadThumbDir)) mkdir($uploadThumbDir);
			
			$imageFileName = substr($_GET["path"], strrpos($_GET["path"], "/") + 1);
			
			$jpeg_quality = 90;
	
			$src = $uploadFileDir . $imageFileName;
			
			$targ_w = $_POST['w'];
			$targ_h = $_POST['h'];
			
			$img_r = imagecreatefromjpeg($src);
			$dst_r = ImageCreateTrueColor( $targ_w, $targ_h );
	
			imagecopyresampled($dst_r, $img_r, 0, 0, $_POST['x'], $_POST['y'], $targ_w, $targ_h, $_POST['w'], $_POST['h']);
			
			imagejpeg($dst_r, $uploadThumbDir . "/" . $imageFileName, $jpeg_quality);
			
			imagedestroy($dst_r);
			
			$uploadImagePath = "/" . UserInfoUtil::getSite()->getSiteId() . $entity->getDefaultUploadDirectory() . "/thumb/";
			
			$responseObject->result = true;
			$responseObject->imagePath = $uploadImagePath . $imageFileName;
			$this->responseObject = $responseObject;
		}
	}
	
	function TrimmingPage($arg) {
    	WebPage::WebPage();
    			
		$this->addModel("jcropcss", array(
			"rel" => "stylesheet",
			"type" => "text/css",
			"href" => SOY2PageController::createRelativeLink("./js/jcrop/css/jquery.Jcrop.min.css")
		));
		
		$this->addModel("jqueryjs", array(
			"type" => "text/JavaScript",
			"src" => SOY2PageController::createRelativeLink("./js/jquery.js")
		));
		$this->addModel("jqueryuijs", array(
			"type" => "text/JavaScript",
			"src" => SOY2PageController::createRelativeLink("./js/jquery-ui.min.js")
		));
		$this->addModel("commonjs", array(
			"type" => "text/JavaScript",
			"src" => SOY2PageController::createRelativeLink("./js/common.js")
		));
		
		$this->addModel("jcropjs", array(
			"type" => "text/JavaScript",
			"src" => SOY2PageController::createRelativeLink("./js/jcrop/js/jquery.Jcrop.min.js")
		));
		
		$this->addModel("display_jcrop_image", array(
			"visible" => (!isset($this->responseObject))
		));
		
		$this->addImage("jcrop_image", array(
			"src" => $_GET["path"],
			"id" => "target"
		));
		
		$this->addForm("form", array(
			"method" => "post",
		));
		
		$this->addModel("display_jcrop_thumbnail", array(
			"visible" => (isset($this->responseObject))
		));
		
		$this->addImage("jcrop_thumbnail", array(
			"src" => (isset($this->responseObject->imagePath)) ? $this->responseObject->imagePath : "",
		));
		
		$this->addInput("jcrop_thumbnail_path", array(
			"value" => (isset($this->responseObject->imagePath)) ? $this->responseObject->imagePath : "",
			"id" => "thumbnail_path"
		));
	}
}
?>