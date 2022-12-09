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

	function __construct(){
		if(!$this->attrDao) $this->attrDao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
	}

	//商品のタイプを調べる。商品のタイプが小商品の場合は親商品のタイプを調べる
	function checkItemType(SOYShop_Item $item){

		$itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		if($item->getType() == SOYShop_Item::TYPE_DOWNLOAD){
			return true;
		}else{
			if(is_numeric($item->getType())){
				try{
					$parent = $itemDao->getById($item->getType());
					if($parent->getType() == SOYShop_Item::TYPE_DOWNLOAD_GROUP) return true;
				}catch(Exception $e){
					//
				}
			}
		}

		return false;
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
		$timeAttr = (isset($attrs["download_assistant_time"])) ? $attrs["download_assistant_time"] : new SOYShop_ItemAttribute();
		$cntAttr = (isset($attrs["download_assistant_count"])) ? $attrs["download_assistant_count"] : new SOYShop_ItemAttribute();

		return array("timeLimit" => $timeAttr->getValue(), "count" => $cntAttr->getValue());
	}

	function getLimitDate($timeLimit){
		if(is_null($timeLimit) || !strlen($timeLimit) || (int)$timeLimit === 0) return null;
		return self::convertDate(time() + $timeLimit * 60 * 60 * 24);
	}

	private function convertDate($time){
		return mktime(0, 0, 0, date("m", $time), date("d", $time), date("Y", $time)) + 24 * 60 * 59;
	}
}
