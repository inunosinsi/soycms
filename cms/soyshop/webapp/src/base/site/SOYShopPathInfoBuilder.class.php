<?php

class SOYShopPathInfoBuilder extends SOY2_PathInfoPathBuilder{

	var $path;
	var $arguments;
	//var $mapping;
	//var $mappingMode = true;

	function __construct(){
		// $mapping = SOYShop_DataSets::get("site.url_mapping","");
		//
		// foreach($mapping as $id => $array){
		// 	$uri = $array["uri"];
		// 	$this->mapping[$uri] = $id;
		// }
		$pathInfo = (isset($_SERVER['PATH_INFO'])) ? $_SERVER['PATH_INFO'] : "";

		//先頭の「/」と末尾の「/」は取り除く
		$pathInfo = preg_replace('/^\/|\/$/',"",$pathInfo);

		list($this->path, $this->arguments) = self::_parsePath($pathInfo);
	}

	/**
	 * パスからページのURI部分とパラメータ部分を抽出する
	 */
	private function _parsePath($path){

		$uri = "";
		$args = array();

		$_uri = explode("/", $path);
		if(!count($_uri)) return array($uri, $args);

		//check cart application
		if(self::_checkAppPage($path, soyshop_get_cart_uri())){
		//if(is_numeric(strpos($path, ))){
			$uri = soyshop_get_cart_uri();
			$args = explode("/",str_replace($uri,"",$path));
			$args = array_values(array_diff($args, array("")));
			return array($uri,$args);
		}

		//check mypage application
		if(self::_checkAppPage($path, soyshop_get_mypage_uri())){
			$uri = soyshop_get_mypage_uri();
			$args = explode("/",str_replace($uri,"",$path));
			$args = array_values(array_diff($args, array("")));
			return array($uri,$args);
		}

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

		if(count($args) == 1 && $args[0] === ""){
			unset($args[0]);
		}

		//uriが空でargs[0]がある場合はuriにargs[0]の値を入れる
		if(!strlen($uri) && isset($args[0]) && strlen($args[0])){
			$uri = $args[0];

			//args[0]がページャ関係でない場合は先頭の値を除く
			if(strpos($args[0], "page-") !== 0) array_shift($args);
		}

		return array($uri, $args);
	}

	//アプリケーションページのURLのチェック
	private function _checkAppPage($path, $uri){
		if(is_bool(strpos($path, $uri))) return false;

		//アプリケーションページのパスと設定されているURLが一致する場合はtrue
		if($path == $uri) return true;

		// app uri/のURLであるか？ マイページ対策
		if(is_numeric(strpos($path, $uri . "/"))) return true;

		return false;
	}

	/**
	 * mapping -> flag
	 */
	// private function _checkUri($uri){
	//
	// 	if($this->mappingMode){
	// 		//uri
	// 		if(isset($this->mapping[$uri])){
	// 			return $this->mapping[$uri];
	// 		}
	//
	// 	}else{
	// 		static $dao;
	// 		if(!$dao) $dao = SOY2DAOFactory::create("site.SOYShop_PageDAO");
	//
	// 		return $dao->checkUri($uri);
	// 	}
	//
	// 	return false;
	// }

	//$_uriの0番目の引数から候補となるページ一覧を取得する
	private static function _getCandidatePageList($uri){
		$dao = new SOY2DAO();

		//トップページの場合
		if(!strlen($uri) || is_numeric(stripos($uri, "index"))){
			$res = $dao->executeQuery("SELECT uri FROM soyshop_page WHERE uri = '' OR uri = '_home' OR uri LIKE :uri", array(":uri" => "index%"));
		}else{
			//uriが空のページは常に取得しておく
			$res = $dao->executeQuery("SELECT uri FROM soyshop_page WHERE uri = '' OR uri LIKE :uri", array(":uri" => $uri . "%"));
		}

		//候補ページを全て取得(indexから始まるページ以外)
		if(!count($res)) $res = $dao->executeQuery("SELECT uri FROM soyshop_page WHERE uri NOT LIKE :uri", array(":uri" => "index%"));

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
