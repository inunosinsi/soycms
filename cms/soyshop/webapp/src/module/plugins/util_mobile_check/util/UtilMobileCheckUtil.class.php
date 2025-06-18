<?php
/*
 * Created on 2012/05/24
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

class UtilMobileCheckUtil{

	public static function getConfig(){
		return SOYShop_DataSets::get("util_mobile_check.config", array(
			"prefix" => "mb",
			"prefix_i" => "i",
			"css" => 1,
			"cookie" => 0,
			"session" => 5,
			"url" => soyshop_get_site_url() . "mb/item/list",
			"message" => "Go to Mobile Site",
			"redirect" => 1,
			"redirect_iphone" => 0,
			"redirect_ipad" => 0,
			"alternate" => 1
		));
	}

	public static function buildUrl($prefix = null){
		$pathInfo = self::_pathinfo();
		$requestUri = self::_requestUri();

		//サイトID：最初と最後に/を付けておく
		$siteDir = strlen($pathInfo) ? strtr($requestUri, array($pathInfo => "")) : $requestUri ;//strtrのキーは空文字列であってはいけない
		if(strlen($siteDir)){
			if($siteDir[0] !== "/") $siteDir = "/" . $siteDir;
			if(substr($siteDir, -1) !== "/") $siteDir .= "/";
		}else{
			$siteDir = "/";
		}

		//prefixを付ける
		return $siteDir. $prefix . $pathInfo;
	}

	//各キャリアのprefixを除いたREQUEST URIを返す
	public static function removeCarrierPrefixUri($prefix){
		$uri = self::_requestUri();
		if(strrpos($uri, "/" . $prefix) == strlen($uri) - strlen($prefix) - 1){
			return str_replace("/" . $prefix, "", $uri);
		}else{
			return str_replace("/" . $prefix . "/", "/", $uri);
		}
	}

	public static function getRequestUri(){
		return self::_requestUri();
	}

	private static function _requestUri(){
		$uri = rawurldecode($_SERVER['REQUEST_URI']);

		//getの値を取り除く
		if(strpos($uri, "?") !== false) $uri = substr($uri, 0, strpos($uri, "?"));

		//サイトID：最初と最後に/を付けておく
		if($uri[0] !== "/") $uri = "/" . $uri;
		if(substr($uri, -1) !== "/") $uri = $uri . "/";

		return $uri;
	}

	public static function getPathInfo(){
		return self::_pathinfo();
	}

	/**
	 * PATH_INFOを取得する
	 * PATH_INFOをGETで渡す環境があるらしい
	 */
	private static function _pathinfo(){
		if(isset($_SERVER['PATH_INFO'])){
			$path = $_SERVER['PATH_INFO'];
		}elseif(isset($_GET['pathinfo'])){
			$path = $_GET['pathinfo'];
		}else{
			return "";
		}

		//先頭はスラッシュ
		if(strlen($path) && $path[0] !== "/") $path = "/" . $path;
		return $path;
	}

	public static function addQueryString($path){
		//絶対パスにQuery Stringを追加する
		if(isset($_SERVER["QUERY_STRING"]) && strlen($_SERVER["QUERY_STRING"]) > 0){
			$path = rtrim($path, "/");
			$array = explode("&", $_SERVER["QUERY_STRING"]);
			$queries = array();
			foreach($array as $query){
				if(strpos($query, "=")){
					$key = substr($query, 0, strpos($query, "="));
					$value = substr($query, strpos($query, "=") + 1);
				}else{
					$key = $query;
					$value = null;
				}
				$queries[$key] = $value;
			}

			//pathinfoの値はここで除く
			if(array_key_exists("pathinfo", $queries)) unset($queries["pathinfo"]);

			if(count($queries) > 0){
				$querystring = "?";
				$counter = 0;
				foreach($queries as $key => $value){
					if($counter > 0) $querystring .= "&";

					if(isset($value) && strlen($value) > 0){
						$querystring .= $key . "=" . $value;
					}else{
						$querystring .= $key;
					}
					$counter++;
				}

				if(strpos($querystring, session_name() . "=") !== false){
					$querystring = preg_replace("/" . session_name() . "=[A-Za-z0-9]*/", session_name() . "=" . session_id(), $querystring);
				}else{
					$querystring = $querystring;
				}

				$path .= $querystring;
			}
		}

		return $path;
	}

	public static function checkLoop($prefix){
		$path = self::_pathinfo();
		return ( $path === "/" . $prefix || strpos($path, "/" . $prefix . "/") === 0 );
	}

	//多言語化プラグインがすでに実行されているか調べる
	public static function checkMultiLanguage(){
		$uri = self::_requestUri();

		$mobConf = UtilMobileCheckUtil::getConfig();
		$reg = "/" . $mobConf["prefix_i"] . "/";

		SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");
		$lngConf = UtilMultiLanguageUtil::getConfig();
		if(isset($lngConf["check_browser_language_config"])) unset($lngConf["check_browser_language_config"]);
		foreach($lngConf as $conf){
			if(!isset($conf["prefix"]) || strlen($conf["prefix"]) === 0) continue;
			if(strpos($uri, $reg . $conf["prefix"]) === 0) return true;
		}

		return false;
	}

	/**
	 * @return array
	 */
	public static function getPrefixList(){
		$_arr = array("");
		$conf = self::getConfig();
		$_arr[] = $conf["prefix"];
		if(is_bool(array_search($conf["prefix_i"], $_arr))){
			$_arr[] = $conf["prefix_i"];
		}
		return $_arr;
	}
}
