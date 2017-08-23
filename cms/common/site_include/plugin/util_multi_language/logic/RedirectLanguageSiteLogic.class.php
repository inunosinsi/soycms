<?php

class RedirectLanguageSiteLogic extends SOY2LogicBase{
	
	function RedirectLanguageSiteLogic(){
		SOY2::import("site_include.plugin.util_multi_language.util.SOYCMSUtilMultiLanguageUtil");
	}
	
	function getLanguageArterCheck($config){
		$lang = trim($_GET["language"]);
		$language = SOYCMSUtilMultiLanguageUtil::LANGUAGE_JP;
		
		foreach($config as $key => $conf){
			if($conf["is_use"] == SOYCMSUtilMultiLanguageUtil::IS_USE && $lang == $key){
				$language = $lang;
				break;
			}
		}
		
		return $language;
	}
	
	/**
	 * URLにプレフィックスを付けた絶対パスを返す
	 */
	function getRedirectPath($config){
		//REQUEST_URI
		$requestUri = $_SERVER['REQUEST_URI'];
		
		//$_GETの値（QUERY_STRING）を削除しておく
		if(strpos($requestUri, "?") !== false){
			$requestUri = substr($requestUri, 0, strpos($requestUri, "?"));
		}

		//PATH_INFO
		$pathInfo = self::getPathInfo();
		
		//先頭はスラッシュ
		if(strlen($pathInfo) && $pathInfo[0] !== "/"){
			$pathInfo = "/" . $pathInfo;
		}
		
		//リダイレクトループを止める
		if(self::checkLoop($pathInfo, $config)){
			return false;
		}
		
		//プレフィックスを取得(スマホ版も考慮)
		$prefix = self::getPrefix($config);
		
		//サイトID：最初と最後に/を付けておく
		$siteDir = strlen($pathInfo) ? strtr(rawurldecode($requestUri), array($pathInfo => "")) : $requestUri;//strtrのキーは空文字列であってはいけない
		
		//最初と最後に/を付けておく
		if(strpos($siteDir, "/") !== 0){
			$siteDir = "/" . $siteDir;
		}
		if(substr($siteDir, -1) !== "/"){
			$siteDir = $siteDir . "/";
		}
		
		//URLエンコードされたPATH_INFOを取得する
		$pathInfo = str_replace("%2F", "/", rawurlencode($pathInfo));
		
		//prefixが0文字の場合はpathInfoの値から他のprefixがないかを調べる。もしくは他の言語域でないかも調べる
		if(strlen($prefix) === 0 || $prefix === self::getCarrierPrefix() || self::checkPathInfo($pathInfo, $config)){
			$pathInfo = self::removeInsertPrefix($pathInfo, $config);
		}

		//prefixを付ける
		$path = self::convertPath($siteDir, $prefix, $pathInfo);
		
		return self::addQueryString($path);;
	}
	
	//リダイレクトを行う必要があるか調べる
	function checkRedirectPath($path){
		if(!$path) return false;
		
		$path = self::formatPath($path);
		$requestUri = self::formatPath($_SERVER["REQUEST_URI"]);
		
		return ($path !== $requestUri);
	}
	
	//無限ループになるかチェック
	private function checkLoop($path, $config){
		$prefix = $prefix = self::getLanguagePrefix($config);
		
		if((self::isMobile() || self::isSmartPhone()) && strlen(self::getCarrierPrefix())){
			$path = str_replace("/" . SOYCMS_CARRIER_PREFIX, "", $path);
		}
		return ($path === "/" . $prefix || strpos($path, "/" . $prefix . "/") === 0 );
	}
	
	//他の言語域のプレフィックスがPathに入っていないか？
	private function checkPathInfo($pathInfo, $config){
		var_dump($config);
//		if(isset($config["check_browser_language_config"])) unset($config["check_browser_language_config"]);
//		if(isset($config[SOYSHOP_PUBLISH_LANGUAGE])) unset($config[SOYSHOP_PUBLISH_LANGUAGE]);
		
		//スマホのプレフィックスを除く
		if(strpos($pathInfo, "/" . self::getCarrierPrefix() . "/") === 0){
			$pathInfo = str_replace("/" . self::getCarrierPrefix() . "/", "/", $pathInfo);
		}
		
		foreach($config as $lang => $conf){
			if($conf["is_use"] == SOYCMSUtilMultiLanguageUtil::IS_USE && strlen($conf["prefix"])){
				
				if(strpos($pathInfo, "/" . $conf["prefix"] . "/") === 0 || $pathInfo == "/" . $conf["prefix"]){
					return true;
				}
			}
		}
		
		return false;
	}
	
	private function removeInsertPrefix($path, $config){
		//スマホ分のプレフィックスを先に削除
		if((self::isMobile() || self::isSmartPhone()) && strlen(self::getCarrierPrefix())){
			$path = str_replace("/" . SOYCMS_CARRIER_PREFIX, "", $path);
		}
		
		foreach($config as $conf){
			if(!isset($conf["prefix"])) continue;
			if(preg_match('/\/' . $conf["prefix"] . '\//', $path) || $path == "/" . $conf["prefix"]){
				$path = str_replace("/" . $conf["prefix"], "", $path);
			}
		}
		return $path;
	}
	
	private function convertPath($siteDir, $prefix, $pathInfo){
		//パスの結合を行う前にpathInfoからスマホプレフィックスを除く
		if((self::isMobile() || self::isSmartPhone()) && strlen(self::getCarrierPrefix())){
			if($pathInfo === "/" . SOYCMS_CARRIER_PREFIX || strpos($pathInfo, "/" . SOYCMS_CARRIER_PREFIX . "/") === 0){
				$pathInfo = str_replace("/" . SOYCMS_CARRIER_PREFIX, "", $pathInfo);
			}
		}
		
		$path = $siteDir . $prefix . $pathInfo;
		
		//おまじない(util_mobile_utilですでにプレフィックスがついているので、ここで一つ除く)
		if(strpos($path, "/" . $prefix . "/" . $prefix) === 0){
			$path = str_replace("/" . $prefix . "/" . $prefix, "/" . $prefix, $path);
		}
		
		//スラッシュが二つになった場合は一つにする
		$path = str_replace("//", "/", $path);
		
		return $path;
	}
	
	private function formatPath($path){
		if(strpos($path, "/") !== 0){
			$path = "/" . $path;
		}
		
		if(strpos($path, "/?") !== 0){
			$path = str_replace("/?", "?", $path);
		}
		
		return $path;		
	}
	
	private function getPrefix($config){
		$prefix = self::getLanguagePrefix($config);
		
		//ガラケーページもしくはスマホページを見ている場合、requestUriにスマホページ分も考慮する
		if((self::isMobile() || self::isSmartPhone())){
			if(strlen($prefix) && strlen(self::getCarrierPrefix())){
				$prefix = SOYCMS_CARRIER_PREFIX . "/" . $prefix;
			}else{
				$prefix = SOYCMS_CARRIER_PREFIX;
			}
		}
		
		return $prefix;
	}
		
	private function addQueryString($path){
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
	
	/**
	 * PATH_INFOを取得する
	 * PATH_INFOをGETで渡す環境があるらしい
	 */
	private function getPathInfo(){
		if(isset($_SERVER['PATH_INFO'])){
			$pathInfo = $_SERVER['PATH_INFO'];
		}elseif(isset($_GET['pathinfo'])){
			$pathInfo = $_GET['pathinfo'];
		}else{
			$pathInfo = null;
		}
		return $pathInfo;
	}
	
	private function isMobile(){
		return (defined("SOYCMS_IS_MOBILE") && SOYCMS_IS_MOBILE);
	}
	
	private function isSmartPhone(){
		return (defined("SOYCMS_IS_SMARTPHONE") && SOYCMS_IS_SMARTPHONE);
	}
	
	private function getCarrierPrefix(){
		return (defined("SOYCMS_CARRIER_PREFIX") && strlen(SOYCMS_CARRIER_PREFIX)) ? SOYCMS_CARRIER_PREFIX : null;
	}
	
	private function getLanguagePrefix($config){
		if(!isset($config[SOYCMS_PUBLISH_LANGUAGE]["is_use"])) return "";
		if($config[SOYCMS_PUBLISH_LANGUAGE]["is_use"] == SOYCMSUtilMultiLanguageUtil::NO_USE) return "";
		
		return (isset($config[SOYCMS_PUBLISH_LANGUAGE]["prefix"])) ? $config[SOYCMS_PUBLISH_LANGUAGE]["prefix"] : "";
		
	}
}
?>