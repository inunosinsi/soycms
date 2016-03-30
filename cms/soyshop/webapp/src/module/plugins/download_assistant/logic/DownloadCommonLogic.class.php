<?php

class DownloadCommonLogic extends SOY2LogicBase{
	
	private $attrDao;
	
	//登録可能な拡張子
	private $allowExtensions = array(
								".zip" => "application/zip",
								".epub" => "application/epub+zip",
								".pdf" => "application/pdf",
								".mp3" => "audio/mpeg",
								".mp4" => "application/mp4"
							);
	
	function DownloadCommonLogic(){
		if(!$this->attrDao) $this->attrDao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
	}
	
	/**
	 * 登録するファイルの拡張子をチェック
	 * @param string
	 * @return boolean
	 */
	function checkFileType($file){
		
		$flag = false;
		foreach($this->allowExtensions as $key => $value){
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
		$extension = strtolower(substr($fileName, strrpos($fileName, ".")));
		return (isset($this->allowExtensions[$extension])) ? $this->allowExtensions[$extension] : "application/octet-stream";
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
		foreach($this->allowExtensions as $key => $value){
			$text[] = "<strong>" . $key . "</strong>";
		}
		return implode(",&nbsp;", $text);
	}
	
	function getDownloadFieldConfig($itemId){

		try{
			$attrs = $this->attrDao->getByItemId($itemId);
		}catch(Exception $e){
			echo $e->getPDOExceptionMessage();
		}
		
		return array("timeLimit" => $attrs["download_assistant_time"]->getValue(), "count" => $attrs["download_assistant_count"]->getValue());
	}
	
	function getLimitDate($timeLimit){
		if(is_null($timeLimit) || !strlen($timeLimit) || (int)$timeLimit === 0) return null;
		return self::convertDate(time() + $timeLimit * 60 * 60 * 24);
	}
	 
	private function convertDate($time){
		return mktime(0, 0, 0, date("m", $time), date("d", $time), date("Y", $time)) + 24 * 60 * 59;
	}
}
?>