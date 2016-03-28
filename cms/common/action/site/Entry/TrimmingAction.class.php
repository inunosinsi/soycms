<?php

/**
 * エントリーを削除します
 */
class TrimmingAction extends SOY2Action{

    function execute($response,$form,$request) {
/**
//		if($form->hasError()){
//			foreach($form as $key => $value){
//				$this->setErrorMessage($key,$form->getErrorString($key));
//			}
//			return SOY2Action::FAILED;
//		}

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
		
		$this->setAttribute("result", $responseObject);
		return SOY2Action::SUCCESS;
**/
	}
}
?>