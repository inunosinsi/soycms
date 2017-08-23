<?php
SOY2::import("util.CMSFileManager");
class FileActionPage extends CMSWebPageBase{
	
	const ACTION_REMOVE 	= 60;
	const ACTION_UPLOAD 	= 70;
	const ACTION_RELOAD		= 90;
	const ACTION_GET_FILE_ID = 110;
	
	var $action;
	
	function doPost(){
		global $site;
		
		//error_reporting(0);
		
		$dir = UserInfoUtil::getSiteDirectory();
		$root = str_replace("\\", '/', realpath(UserInfoUtil::getSiteDirectory()));

		
		switch ($this->action) {
			case self::ACTION_REMOVE:
				if(!soy2_check_token()){
					exit;
				}
				
				$fileIds = $_POST["ids"];
				foreach($fileIds as $fileId){
					try{
						$parentId = CMSFileManager::removeFile($root, $fileId);
					}catch (Exception $e){
						
					}
				}

				$result = array(
					"soy2_token" => soy2_get_token(),
				);

				echo json_encode($result);
				break;
			case self::ACTION_UPLOAD:
				if(!soy2_check_token()){
					exit;
				}
				
				$dirId = $_POST["id"];
				$nameArray = $_POST["name"];

				foreach($nameArray as $name){
					try{
						CMSFileManager::insertFile($root, $dirId, $name);
					}catch (Exception $e){
						
					}
				}

				$result = array(
					"soy2_token" => soy2_get_token(),
				);
				
				echo json_encode($result);
				
				break;
			case self::ACTION_RELOAD:
				$dirId = $_POST["id"];
				
				try{
					CMSFileManager::rebuildTree($root, $dirId);
				}catch (Exception $e){
	
				}					

				$result = array(
					"soy2_token" => soy2_get_token()
				);
				
				echo json_encode($result);
				break;
			case self::ACTION_GET_FILE_ID:
			default:
				try{
					$dir = CMSFileManager::get($root, intval($_POST["target"]), true);
				}catch (Exception $e){
					try{
						$target = dirname($root) . "/" . $_POST["target"];
						$dir = CMSFileManager::get($root, $target, true);
					}catch (Exception $e){
						break;
					}
				}

				$dirId = $dir->getId();
				$fileAttrs = $_POST["fileAttrs"];
				foreach($fileAttrs as $key => $fileAttr){
					try{
						$file = CMSFileManager::get($root, $dir->getPath() . "/" . $fileAttr["name"], true);
						$fileAttrs[$key]["id"] = $file->getId();
					}catch (Exception $e){
						// 扱えないファイル形式
						$fileAttrs[$key]["id"] = -1;
					}
				}
				echo json_encode(array(
					"dirId" => $dirId,
					"files" => is_array($fileAttrs) ? $fileAttrs : array(),
				));
				break;
		}
		
		exit;
	
	}
	
    function __construct($args) {
    	$this->action = $args[0];

    	//parent::__construct();
    	$this->doPost();
    }
    
		/*    function createThumbnail($url){
    	
    	$height = null;
    	$width = 100;
    	
    	$extension = preg_replace("/.*\.([a-zA-Z]*)$/",'$1',$url);
		
		$img = null;
	    switch(true){
    		case ('jpg' == strtolower($extension)):
    		case ('jpeg' == strtolower($extension)):
    			$header = 'Content-type: image/jpeg';
    			$img = @imagecreatefromjpeg($url);
    			$functionName = 'imagejpeg';
    			break;
    		case ('gif' == strtolower($extension)):
    			$header = 'Content-type: image/gif';
    			$img = @imagecreatefromgif($url);
    			$functionName = 'imagegif';
    			break;
    		case ('png' == strtolower($extension)):
    			$header = 'Content-type: image/png';
    			$img = @imagecreatefrompng($url);
    			$functionName = 'imagepng';
    			break;
    	}
    	
    	
    	//画像が無かった場合
		if(!$img){
			header($header);
			echo file_get_contents($url);
			exit;				
		}
		
		$tempImage = $img;
			
		//新しい画像の大きさを決める。
		list($oldSize['height'],$oldSize['width']) = array(imagesy($tempImage),imagesx($tempImage));
		$newSize = array();
		if($height && $width){
			$newSize['height'] = $height;
			$newSize['width'] = $width;
		}else if($height){
			$newSize['height'] = $height;
			$newSize['width'] = $oldSize['width'] * $height / $oldSize['height'];
		}else if($width){
			$newSize['height'] = $oldSize['height'] * $width / $oldSize['width'];
			$newSize['width'] = $width;
		}else{
			$newSize = $oldSize;
		}
		
		if($newSize['height'] >= $oldSize['height'] && $newSize['width'] >= $oldSize['width']){
			$newSize = $oldSize;
		}
		
		$dst = imagecreatetruecolor($newSize['width'],$newSize['height']);
		$alpha = imagecolorallocate($dst, 255, 255, 255); // 色の作成
		imagefill($dst, 0, 0, $alpha);                    // 指定色で塗りつぶし
		imagecolortransparent($dst, $alpha);              // 指定色を透明色に
		imagecolordeallocate($dst, $alpha);               // 色リソースの開放
		imagesavealpha($dst, TRUE);
		
		imagecopyresized($dst,$tempImage,0,0,0,0,$newSize['width'],$newSize['height'],$oldSize['width'],$oldSize['height']);
	
		header($header);
		imagepng($dst);
    }*/
}
?>