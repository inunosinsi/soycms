<?php
SOY2::import("util.CMSFileManager");

class UploadFileAction extends SOY2Action{

	private $maxWidth;
	private $ifExists;//auto_rename | dialog デフォルトはdialog

    function execute($resp, $form, $req) {
		$responseObject = new StdClass();
		$responseObject->clearfileresult = false;
		$responseObject->result = false;
		$responseObject->message = "";
		$responseObject->errorCode = 0;
		$responseObject->filepath = "";
		$responseObject->serverpath = "";
		$responseObject->mode = "prepare";

		//エラーチェック @TODO 多言語対応
		if(isset($_FILES) && is_array($_FILES) && isset($_FILES['file']) && is_array($_FILES['file'])){
			if(isset($_FILES['file']['error']) && $_FILES['file']['error'] != UPLOAD_ERR_OK){
				switch($_FILES['file']['error']){
					case UPLOAD_ERR_INI_SIZE:
						$message = "ファイルサイズが設定された制限値を越えています。".ini_get("upload_max_filesize")."までアップロードできます。";
						break;
					case UPLOAD_ERR_FORM_SIZE:
						$message= "ファイルサイズがフォームで設定された制限値を越えています。";
						break;
					case UPLOAD_ERR_PARTIAL:
						$message= "ファイルの一部しかアップロードされていません。";
						break;
					case UPLOAD_ERR_NO_FILE:
						$message= "ファイルがアップロードされていません。";
						break;
					case UPLOAD_ERR_NO_TMP_DIR:
						$message= "アップロード用の一時ディレクトリが存在しません。";
						break;
					case UPLOAD_ERR_CANT_WRITE:
						$message= "アップロード用の一時ディレクトリに書き込みできません。";
						break;
					case UPLOAD_ERR_EXTENSION:
						$message= "拡張モジュールがファイルのアップロードを中止しました。";
						break;
					default:
						$message= "ファイルのアップロードで原因不明のエラーが発生しました。";
						break;
				}
				$responseObject->result = false;
				$responseObject->message = $message;
				$responseObject->errorCode = 101;
				$this->setAttribute("result", $responseObject);
				return;
			}

			//MIMETYPE
			if(is_bool(array_search($_FILES['file']["type"], CMSFileManager::getAllowedMimeTypes()))){
				$responseObject->result = false;
				$responseObject->message = "許可されていないMIMETYPEです。";
				$responseObject->errorCode = 101;
				$this->setAttribute("result", $responseObject);
				return;
			}
		}

		//エラー POSTされてこなかった状態 post_max_sizeなど
		if(isset($_FILES) && is_array($_FILES) && count($_FILES) == 0){
			$responseObject->result = false;
			$responseObject->message = "ファイルがアップロードされていません。";
			$responseObject->errorCode = 100;
			$this->setAttribute("result", $responseObject);
			return;
		}

		$responseObject->result = false;
		$responseObject->message = "ファイルのアップロードでエラーが発生しました。";
		$responseObject->errorCode = 100;

    	//前回アップロードしたファイルを削除
    	$beforepath = UserInfoUtil::getSiteDirectory().$this->getDefaultUpload().$form->getBeforepath();
    	if(strlen($form->getBeforepath()) != 0 && file_exists($beforepath)){
    		if(unlink($beforepath)){
    			$responseObject->clearfileresult = true;
    		}else{
    			$responseObject->clearfileresult = false;
    		}
    	}else{
    		$responseObject->clearfilereult = false;
    	}

    	if(strlen($form->getAlter_name()) != 0){
    		$filename = str_ireplace('..','',$form->getAlter_name());
    		$_FILES['file']['name'] = $filename;
    	}else{
    		$filename = $_FILES['file']['name'];
    	}

		//ファイル名の変更
		$onLoads = CMSPlugin::getEvent('onFileUploadConvertFileName');
		if(count($onLoads)){
			foreach($onLoads as $plugin){
				$func = $plugin[0];
				$res = call_user_func($func, array('filename' => $filename));
				if(is_string($res)) $filename = $res;
			}
		}

    	//パス, URL
		$filepath = UserInfoUtil::getSiteDirectory() .$this->getDefaultUpload() . "/". $filename;

		//自動連番 photo.jpg -> photo_99.jpg
		$counter = 1;
		$original_name = $filename;
		while($this->ifExists == "auto_rename" && file_exists($filepath) && $counter++ < 100){
			$filename = $this->getNextFileName($original_name, $counter);
			$filepath = UserInfoUtil::getSiteDirectory() .$this->getDefaultUpload() . "/". $filename;
		}
		$_FILES['file']['name'] = $filename;

		$responseObject->filepath = self::getSiteUrl() . $this->getDefaultUpload() .  "/". rawurlencode($filename);

    	//一時ファイルにしたほうがいいかも
    	//サーバー内のファイルパス
    	$responseObject->serverpath = $filename;

    	//準備段階の動作であることを示す(applyは挿入決定)
    	$responseObject->mode = "prepare";
		if(!file_exists(UserInfoUtil::getSiteDirectory().$this->getDefaultUpload())){
			$responseObject->result = false;
			$responseObject->message = "保存先ディレクトリが見つかりません";
			$responseObject->errorCode = 3;
		}else if(file_exists($filepath)){
    		$responseObject->result = false;
    		$responseObject->message = "すでにファイルが存在します";
    		$responseObject->errorCode = 1;
    	}else{
	    	
	    	//サイトの情報を設定
	    	//$site = UserInfoUtil::getSite();
	    	//$url = (UserInfoUtil::getSiteURLBySiteId($site->getId()) != $site->getUrl() ) ? $site->getUrl() : null;
	    	
	    	if(CMSFileManager::upload(UserInfoUtil::getSiteDirectory(), $this->getDefaultUpload(), $_FILES['file'])){
				$responseObject->result = true;
				$responseObject->message = "成功しました";

				// typeはmimetypeからスラッシュより前の部分(image/jpegであればimage)を取得する
				$responseObject->type = trim(trim(substr($_FILES['file']["type"], 0, strpos($_FILES['file']["type"], "/")), "/"));
				if($responseObject->type == "image"){
					if($imageSize = soy2_image_info($filepath)){
						$responseObject->imageWidth = $imageSize["width"];
						$responseObject->imageHeight = $imageSize["height"];

						//リサイズ
						if($this->maxWidth && $this->maxWidth < $responseObject->imageWidth){
							if(soy2_resizeimage($filepath,$filepath,$this->maxWidth)){
								$responseObject->imageWidth  = $this->maxWidth;
								$responseObject->imageHeight = $imageSize["height"] * $this->maxWidth / $imageSize["width"];
							}
						}
					}

					//jpegoptim
					$ext = trim(trim(substr($_FILES['file']["type"], strpos($_FILES['file']["type"], "/")), "/"));
					switch($ext){
						case "jpeg":
							exec("jpegoptim -V", $out);
							if(is_array($out) && count($out)){
								exec("jpegoptim --strip-all " . escapeshellarg($filepath));
							}
							break;
					}
				}
	    	}else{
	    		$responseObject->result = false;
	    		$responseObject->message = "ファイル移動で原因不明のエラーが発生しました";
	    		$responseObject->errorCode = 2;
	    	}
    	}
    	$this->setAttribute("result",$responseObject);
    }

	private function getSiteUrl(){
		$url = UserInfoUtil::getSiteURL();
		$siteId = UserInfoUtil::getSite()->getSiteId();
		if(is_numeric(strpos($url, $_SERVER["HTTP_HOST"])) && !strpos($url, "/" . $siteId . "/")){
			$url = rtrim($url, "/") . "/" . $siteId . "/";
		}
		return $url;
	}

	/**
	 * アップロードディレクトリのパスを返す
	 * 最初にも最後にもスラッシュは付かない
	 */
    function getDefaultUpload(){
		// 空文字列または/dir/**/path
		$dir = soycms_get_site_config_object()->getUploadDirectory();

		//先頭の/を削除
		if(strlen($dir) && $dir[0] == "/") $dir = substr($dir,1);

		return $dir;
    }

    function getNextFileName(string $filename, int $counter){
    	if(is_numeric(strpos($filename,"."))){
	    	$base = substr($filename,0,strrpos($filename,"."));
	    	$ext  = substr($filename,strrpos($filename,"."));
    	}else{
    		$base = $filename;
    		$ext  = "";
    	}

    	return $base."_".sprintf("%02d",$counter).$ext;
    }

    public function setMaxWidth($v){
    	$this->maxWidth = $v;
    }

    public function setIfExists($v){
    	$this->ifExists = $v;
    }
}

class UploadFileActionForm extends SOY2ActionForm{

	private $beforepath;
	private $alter_name;

	function getBeforepath(){
		return $this->beforepath;
	}
	function setBeforepath($beforpath){
		$this->beforepath = $beforpath;
	}

	function getAlter_name(){
		return $this->alter_name;
	}
	function setAlter_name($alter_name){
		$this->alter_name = $alter_name;
	}
}
