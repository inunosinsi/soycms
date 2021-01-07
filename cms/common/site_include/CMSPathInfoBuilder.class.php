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
		$uri = "";
		$args = array();

		$_uri = explode("/", $path);
		if(!count($_uri)) return array($uri, $args);

		//$_uriからページの候補を挙げる $_uriの値が2個以上ある場合はブログページである可能性が高い
		$candidateList = self::_getCandidatePageList($_uri[0]);
		if(!count($candidateList) && count($_uri) === 1) return array($_uri[0], $args);

		while(count($_uri)){
			$baseuri = implode("/", $_uri);
			if(is_numeric(array_search($baseuri, $candidateList))){
				$uri = $baseuri;
				break;
			}

			if(strlen($baseuri)) $baseuri .= "/";

			// path/index.htmlも試す
			if(is_numeric(array_search($baseuri."index.html", $candidateList))){
				$uri = $baseuri."index.html";
				break;
			}

			// path/index.htmも試す
			if(is_numeric(array_search($baseuri."index.htm", $candidateList))){
				$uri = $baseuri."index.htm";
				break;
			}

			// path/index.htmも試す
			if(is_numeric(array_search($baseuri."index.php", $candidateList))){
				$uri = $baseuri."index.php";
				break;
			}

			//uriの末尾をargsに移す
			array_unshift($args, array_pop($_uri));
		}

		//uriとargsを書き換える拡張ポイント
		$onLoad = CMSPlugin::getEvent('onPathInfoBuilder');
		foreach($onLoad as $plugin){
			$func = $plugin[0];
			$res = call_user_func($func, array('uri' => $uri, 'args' => $args));

			if(isset($res["uri"])) $uri = $res["uri"];
			if(isset($res["args"])) $args = $res["args"];
		}

		return array($uri, $args);
	}

	//$_uriの0番目の引数から候補となるページ一覧を取得する
	private static function _getCandidatePageList($uri){
		$dao = new SOY2DAO();

		//トップページの場合
		$pos = stripos($uri, "index");
		if(!strlen($uri) || is_numeric($pos)){
			// _index***の可能性を加味する	高速化の為に条件によってbindを分ける
			$bind = ($pos > 0) ? "%index%" : "index%";
			$res = $dao->executeQuery("SELECT uri FROM Page WHERE uri = '' OR uri LIKE :uri", array(":uri" => $bind));
		}else{
			//uriが空のページは常に取得しておく
			$res = $dao->executeQuery("SELECT uri FROM Page WHERE uri = '' OR uri LIKE :uri", array(":uri" => $uri . "%"));
		}

		//候補ページを全て取得(indexから始まるページ以外)
		if(!count($res)) $res = $dao->executeQuery("SELECT uri FROM Page WHERE uri NOT LIKE :uri", array(":uri" => "index%"));

		if(!count($res)) return array();

		$list = array();
		foreach($res as $v){
			$list[] = $v["uri"];
		}
		return $list;
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
