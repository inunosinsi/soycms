<?php

class CMSPathInfoBuilder extends SOY2_PathInfoPathBuilder{

	var $path;
	var $arguments;

	function __construct(){
		$pathInfo = (isset($_SERVER['PATH_INFO'])) ? $_SERVER['PATH_INFO'] : "";

		//先頭の「/」と末尾の「/」は取り除く
		$pathInfo = preg_replace('/^\/|\/$/', "", $pathInfo);

		list($this->path, $this->arguments) = self::parsePath($pathInfo);
	}

	/**
	 * パスからページのURI部分とパラメータ部分を抽出する
	 */
	public static function parsePath($path){
		static $dao;
		if(!$dao) $dao = SOY2DAOFactory::create("cms.PageDAO");

		$_uri = explode("/", $path);

		$uri = "";
		$args = array();

		while(count($_uri)){
			$baseuri = implode("/", $_uri);

			$testUri = $baseuri;
			if($dao->checkUri($testUri)){
				$uri = $testUri;
				break;
			}

			// path/index.htmlも試す
			$testUri = $baseuri."/index.html";
			if($dao->checkUri($testUri)){
				$uri = $testUri;
				break;
			}

			// path/index.htmも試す
			$testUri = $baseuri . "/index.htm";
			if($dao->checkUri($testUri)){
				$uri = $testUri;
				break;
			}

			// path/index.phpも試す
			$testUri = $baseuri . "/index.php";
			if($dao->checkUri($testUri)){
				$uri = $testUri;
				break;
			}

			//uriの末尾をargsに移す
			array_unshift($args, array_pop($_uri));
		}

		if(!strlen($uri)){
			//uriが空の時でargsの値が1の時はargs[0]をuriに持ってくる。argsの値が2以上の場合はブログページである可能性が高い
			if(count($args) === 1 && $args[0] != "feed" && strpos($args[0], "page-") === false) {
				$uri = $args[0];

				//まだuriが空の場合は、index.html、index.htmやindex.phpを試す
				if($dao->checkUri("index.html")){
					$uri = "index.html";
				}else if($dao->checkUri("index.htm")){
					$uri = "index.htm";
				}else if($dao->checkUri("index.php")){
					$uri = "index.php";
				}
			}
		}

		return array($uri, $args);
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
		if(preg_match("/^https?:/", $path)){
			return $path;
		}

		/**
		 * 絶対パスが渡されたときもそのまま返す
		 */
		if(preg_match("/^\//", $path)){
			if($isAbsoluteUrl){
				return $scheme . "://" . $host . $port . $path;
			}else{
				return $path;
			}
		}

		/**
		 * 相対パス（絶対URL、絶対パス以外）のとき
		 */
		//フロントコントローラーのURLでの絶対パス（ファイル名index.phpは削除する）
		$scriptPath = (isset($_SERVER['SCRIPT_NAME']) && strlen($_SERVER['SCRIPT_NAME']) != 0) ? $_SERVER['SCRIPT_NAME'] : "/";
		if($scriptPath[strlen($scriptPath) - 1] == "/"){
			//サーバーによってはindex.phpが付かないところもあるようだ（Ablenet）
		}else{
			$scriptPath = preg_replace("/" . basename($scriptPath) . "\$/", "", $scriptPath);
		}

		$url = self::convertRelativePathToAbsolutePath($path, $scriptPath);

		if($isAbsoluteUrl){
			return $scheme . "://" . $host . $port . $url;
		}else{
			return $url;
		}
	}
}
