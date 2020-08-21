<?php

class HTMLCacheUtil{

	const ON = 1;
	const OFF = 0;

	public static function getPageDisplayConfig(){
		$cnf = SOYShop_DataSets::get("x_html_cache.config", null);
		if(!is_null($cnf)) return $cnf;
		$pages = self::_getPages();

		//
		$cnf = array();
		foreach($pages as $page){
			$cnf[$page->getId()] = self::OFF;
		}

		return $cnf;
	}

	public static function savePageDisplayConfig($values){

		$pages = self::_getPages();

		$cnf = array();
		foreach($pages as $page){
			$pageId = $page->getId();
			$cnf[$pageId] = (in_array($pageId, $values)) ? self::ON : self::OFF;
		}

		SOYShop_DataSets::put("x_html_cache.config", $cnf);
	}

	private static function _getPages(){
		try{
			return SOY2DAOFactory::create("site.SOYShop_PageDAO")->get();
		}catch(Exception $e){
			return array();
		}
	}

	/** キャッシュの生成周り **/
	public static function generateStaticHTMLCacheFile($html){
		$pathInfo = (isset($_SERVER["PATH_INFO"])) ? $_SERVER["PATH_INFO"] : "_top";
		$alias = trim(substr($pathInfo, strrpos($pathInfo, "/")), "/");

		$dir = self::_cacheDir();
		if(!file_exists($dir)) mkdir($dir);

		if(is_numeric($alias)){
			$dir .= "n/";
			if(!file_exists($dir)) mkdir($dir);
		}else{
			$dir .= "s/";
			if(!file_exists($dir)) mkdir($dir);
		}

		$hash = md5($pathInfo);
		for($i = 0; $i < 10; ++$i){
			$dir .= substr($hash, 0, 1) . "/";
			if(!file_exists($dir)) mkdir($dir);
			$hash = substr($hash, 1);
		}

		file_put_contents($dir . $hash . ".html", $html);
	}

	public static function removeCacheFiles(){
		$dir = self::_cacheDir();
		if(file_exists($dir)) self::_deleteDir($dir);
	}

	private static function _deleteDir($path){
		//ディレクトリ指定でスラッシュがあれば除去
		$path = rtrim($path, "/");
		//指定されたディレクトリの中身一覧取得
		$list = glob($path . "/*");

		foreach($list as $key => $value){
			//ディレクトリ(フォルダ)なら再帰呼出し
			if(is_dir($value)){
				self::_deleteDir($value);
			//ファイルなら削除
			}else{
				unlink($value);
			}
		}

		//指定されたディレクトリの中身が空ならディレクトリ削除して終了
		$list = glob($path . "/*");
		if(count($list) === 0){
			rmdir($path);
			return;
		}
	}

	private static function _cacheDir(){
		return SOYSHOP_SITE_DIRECTORY . ".cache/static_cache/";
	}
}
