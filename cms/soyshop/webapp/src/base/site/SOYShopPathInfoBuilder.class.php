<?php

class SOYShopPathInfoBuilder extends SOY2_PathInfoPathBuilder{

	var $path;
	var $arguments;
	var $mapping;
	var $mappingMode = true;

	function SOYShopPathInfoBuilder(){

		$mapping = SOYShop_DataSets::get("site.url_mapping","");

		foreach($mapping as $id => $array){
			$uri = $array["uri"];
			$this->mapping[$uri] = $id;
		}

		$pathInfo = (isset($_SERVER['PATH_INFO'])) ? $_SERVER['PATH_INFO'] : "";

		//先頭の「/」と末尾の「/」は取り除く
		$pathInfo = preg_replace('/^\/|\/$/',"",$pathInfo);

		list($this->path, $this->arguments) = $this->parsePath($pathInfo);



	}

	/**
	 * パスからページのURI部分とパラメータ部分を抽出する
	 */
	function parsePath($path){

		$_uri = explode("/", $path);

		$uri = "";
		$args = array();

		//check cart application
		if( count($_uri)>0 && $_uri[0] === soyshop_get_cart_uri() ){
			$uri = soyshop_get_cart_uri();
			$args = explode("/",str_replace($uri,"",$path));
			$args = array_values(array_diff($args, array("")));
			return array($uri,$args);
		}

		//check mypage application
		if( count($_uri)>0 && $_uri[0] === soyshop_get_mypage_uri() ){
			$uri = soyshop_get_mypage_uri();
			$args = explode("/",str_replace($uri,"",$path));
			$args = array_values(array_diff($args, array("")));
			return array($uri,$args);
		}

		while(count($_uri)){
			$baseuri = implode("/", $_uri);

			$testUri = $baseuri;

			if(false !== $this->checkUri($testUri)){
				$uri = $testUri;
				break;
			}

			// path/index.htmlも試す
			$testUri = $baseuri."/index.html";
			if(false !== $this->checkUri($testUri)){
				$uri = $testUri;
				break;
			}

			// path/index.htmも試す
			$testUri = $baseuri."/index.htm";
			if(false !== $this->checkUri($testUri)){
				$uri = $testUri;
				break;
			}

			//uriの末尾をargsに移す
			array_unshift($args, array_pop($_uri));
		}

		if(count($args) == 1 && $args[0] === ""){
			unset($args[0]);
		}

		return array($uri, $args);
	}

	/**
	 * mapping -> flag
	 */
	function checkUri($uri){

		if($this->mappingMode){

			//uri
			if(isset($this->mapping[$uri])){
				return $this->mapping[$uri];
			}

		}else{
			static $dao;
			if(!$dao) $dao = SOY2DAOFactory::create("site.SOYShop_PageDAO");

			return $dao->checkUri($uri);

		}

		return false;
	}

	/**
	 * フロントコントローラーからの相対パスを解釈してURLを生成する
	 */
	function createLinkFromRelativePath($path, $isAbsoluteUrl = false){
		//scheme
		$scheme = (isset($_SERVER["HTTPS"]) || defined("SOY2_HTTPS") && SOY2_HTTPS) ? "https" : "http";

		//port
		if( $_SERVER["SERVER_PORT"] == "80" && !isset($_SERVER["HTTPS"]) || $_SERVER["SERVER_PORT"] == "443" && isset($_SERVER["HTTPS"]) ){
			$port = "";
		}elseif(strlen($_SERVER["SERVER_PORT"]) > 0){
			$port = ":" . $_SERVER["SERVER_PORT"];
		}else{
			$port = "";
		}

		//host (domain)
		$host = $_SERVER["SERVER_NAME"];

		/**
		 * 絶対URLが渡されたらそのまま返す
		 */
		if(preg_match("/^https?:/",$path)){
			return $path;
		}

		/**
		 * 絶対パスが渡されたときもそのまま返す
		 */
		if(preg_match("/^\//",$path)){
			if($isAbsoluteUrl){
				return $scheme."://" . $host.$port.$path;
			}else{
				return $path;
			}
		}

		/**
		 * 相対パス（絶対URL、絶対パス以外）のとき
		 */
		//フロントコントローラーのURLでの絶対パス（ファイル名index.phpは削除する）
		$scriptPath = (isset($_SERVER['SCRIPT_NAME'])) ? $_SERVER['SCRIPT_NAME'] : "/";
		if($scriptPath[strlen($scriptPath)-1] == "/"){
			//サーバーによってはindex.phpが付かないところもあるようだ（Ablenet）
		}else{
			$scriptPath = preg_replace("/".basename($scriptPath)."\$/","",$scriptPath);
		}

		$url = self::convertRelativePathToAbsolutePath($path, $scriptPath);

		if($isAbsoluteUrl){
			return $scheme."://" . $host.$port.$url;
		}else{
			return $url;
		}
	}



}
?>