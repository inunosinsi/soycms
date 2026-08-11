<?php

class DownloadCommonLogic extends SOY2LogicBase{

	//登録可能な拡張子
	private $allowExtensions = array(
		".zip" => "application/zip",
		".epub" => "application/epub+zip",
		".pdf" => "application/pdf",
		".mp3" => "audio/mpeg",
		".mp4" => "application/mp4"
	);

	function __construct(){}

	//商品のタイプを調べる。商品のタイプが小商品の場合は親商品のタイプを調べる
	function checkItemType(SOYShop_Item $item){

		$itemDao = soyshop_get_hash_table_dao("item");
		if($item->getType() == SOYShop_Item::TYPE_DOWNLOAD) return true;
		if(!is_numeric($item->getType())) return false;

		// parent item object
		if(soyshop_get_item_object((int)$item->getType()) == SOYShop_Item::TYPE_DOWNLOAD_GROUP) return true;

		return false;
	}

	/**
	 * 登録するファイルの拡張子をチェック
	 * @param string
	 * @return bool
	 */
	function checkFileType(string $file){
		foreach($this->allowExtensions as $key => $value){
			if(preg_match('/' . $key . '$/', $file)){
				return true;
			}
		}
		return false;
	}

	/**
	 * ダウンロードするファイルのcontent-typeを取得する
	 * @param string filename
	 * @return string extenstion
	 */
	function getContentType(string $fileName){
		$extension = strtolower(substr($fileName, strrpos($fileName, ".")));
		return (isset($this->allowExtensions[$extension])) ? $this->allowExtensions[$extension] : "application/octet-stream";
	}

	/**
	 * zipファイルのサイズを取得する
	 * 他のファイル形式でも可能
	 * @return int size
	 */
	function getFileSize(int $size){
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

	/**
	 * @param int, bool
	 * @return array
	 */
	function getDownloadFieldConfig(int $itemId, bool $isAdmin=false){
		$attrs = soyshop_get_item_attribute_objects($itemId, array("download_assistant_time", "download_assistant_count"));
		$time = $attrs["download_assistant_time"]->getValue();
		$count = $attrs["download_assistant_count"]->getValue();

		// 商品情報の初回登録
		if($isAdmin && is_null($time) && is_null($count)){			
			SOY2::import("module.plugins.download_assistant.common.DownloadAssistantCommon");
			$config = DownloadAssistantCommon::getConfig();
			$time = (isset($config["timeLimit"])) ? $config["timeLimit"] : null;
			$count = (isset($config["count"]))? $config["count"] : null;
		}

		if($time == 0) $time = null;
		if($count == 0) $count = null;

		return array(
			"timeLimit" => (is_numeric($time)) ? (int)$time : null, 
			"count" => (is_numeric($count)) ? (int)$count : null
		);
	}

	/**
	 * @param int|null
	 * @return int|null
	 */
	function getLimitDate($timeLimit){
		if(is_null($timeLimit) || !strlen($timeLimit) || (int)$timeLimit === 0) return null;
		return self::convertDate(time() + $timeLimit * 60 * 60 * 24);
	}

	/**
	 * @param int
	 * @return int
	 */
	private function convertDate(int $time){
		return mktime(0, 0, 0, date("m", $time), date("d", $time), date("Y", $time)) + 24 * 60 * 59;
	}
}
