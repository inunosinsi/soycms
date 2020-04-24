<?php

class GenerateBarcodeUtil {

	//生成するバーコードの格納ディレクトリ
	public static function getBarcodeDirectory(){
		return self::_getBarcodeDirectory();
	}

	public static function getBarcodeImagePath($filename){
		//画像ファイルが存在しているか？を調べてからパスを返す
		if(file_exists(self::_getBarcodeDirectory() . $filename)){
			return "/" . SOYSHOP_ID . "/files/barcode/" . $filename;
		}else{
			return null;
		}
	}

	private static function _getBarcodeDirectory(){
		$dir = SOYSHOP_SITE_DIRECTORY . "files/";
		if(!file_exists($dir)) mkdir($dir);
		$dir .= "barcode/";
		if(!file_exists($dir)) mkdir($dir);
		return $dir;
	}
}
