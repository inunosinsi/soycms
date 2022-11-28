<?php

class BonusDownloadFileLogic {
	
	//登録可能な拡張子
	private $allowExtension = array(
								".zip"=>"application/zip",
								".epub"=>"application/epub+zip",
								".pdf"=>"application/pdf",
								".mp3"=>"audio/mpeg",
								".mp4"=>"application/mp4"
							);
	
	/**
	 * 登録するファイルの拡張子をチェック
	 * @param string
	 * @return boolean
	 */
	function checkFileType($file){
		
		$flag = false;
		foreach($this->allowExtension as $key => $value){
			if(preg_match('/' . $key . '$/', $file)){
				$flag = true;
				break;
			}
		}
		return $flag;
	}
	
	/**
	 * ダウンロードするファイルのcontent-typeを取得する
	 * @param string filename
	 * @return string extenstion
	 */
	function getContentType($fileName){
		$extension = substr($fileName, strrpos($fileName, "."));
		return (isset($this->allowExtension[$extension])) ? $this->allowExtension[$extension] : "application/octet-stream";
	}
	
	/**
	 * zipファイルのサイズを取得する
	 * 他のファイル形式でも可能
	 * @return int size
	 */
	function getFileSize($size){
		$sizes = array('Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB');
		  $ext = $sizes[0];
    				
    	for ($i=1; (($i <count($sizes)) && ($size>= 1024)); $i++) {
    	   	$size = $size / 1024;
        	$ext = $sizes[$i];
    	}
    	return round($size, 2) . $ext;
	}
	
	/**
	 * ダウンロード可能な拡張子を表示する
	 */
	function allowExtension(){
		$text = array();
		foreach($this->allowExtension as $key => $value){
			$text[] = "<strong>" . $key . "</strong>";
		}
		return implode(",&nbsp;", $text);
	}
	
	/**
	 * 共通の設定
	 */
	function getDownloadConfig(){
    	return SOYShop_DataSets::get("download_assistant.config", array(
			"timeLimit" => null,
			"count" => null,
			"allow" => 1,
			"mail" => "※ダウンロード\nダウンロードは下記のURLから行えます。\n##DOWNLOAD_URL##\n\n\n"
		));
    }
    
    /* ファイルアップロード */
    
    /**
     * チェック
     * @param array $file
     * @return boolean エラーなしならtrue
     */
    function checkUpload($file){
    	$res = true;
    	
    	//@TODO 拡張子チェック
    	
    	//@TODO ファイル名チェック 半角英数
    	
    	
    	return $res;
    }
    
    /**
     * アップロード処理
     * @param array $file
     * @return boolean
     */
    function uploadFile($file){
    	
    	//ファイルアップロード
    	$dir = BonusDownloadConfigUtil::getUploadDir();
    	if(!is_dir($dir)) mkdir($dir);
    	
    	
    	$filename = $file["name"];
    	return @move_uploaded_file($file["tmp_name"], $dir. $filename); 
    }
    
    /**
     * ファイル削除処理
     * @param string $filename ファイル名
     */
    function deleteFile($filename){
    	$dir = BonusDownloadConfigUtil::getUploadDir();
    	$path = $dir. $filename;
    	if(is_file($path)){
    		unlink($path);
    	}
    	
    }
    
    
    
}
?>