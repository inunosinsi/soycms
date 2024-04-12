<?php

class RedirectLanguageSiteLogic extends SOY2LogicBase{

	private $config;
	
	function RedirectLanguageSiteLogic(){
		SOY2::import("site_include.plugin.util_multi_language.util.SOYCMSUtilMultiLanguageUtil");
	}
	
	/**
	 * @return string
	 */
	function getLanguageArterCheck(){
		$lang = trim($_GET["language"]);
		
		$cnf = $this->config;
		if(!count($cnf)) return SOYCMSUtilMultiLanguageUtil::LANGUAGE_JP;

		foreach($cnf as $key => $conf){
			if($conf["is_use"] == SOYCMSUtilMultiLanguageUtil::IS_USE && $lang == $key){
				return $lang;
			}
		}
		
		return SOYCMSUtilMultiLanguageUtil::LANGUAGE_JP;
	}
	
	/**
	 * URLにプレフィックスを付けた絶対パスを返す
	 */
	function getRedirectPath(){
		$cnf = $this->config;

		//REQUEST_URI
		$requestUri = $_SERVER['REQUEST_URI'];
		
		//$_GETの値（QUERY_STRING）を削除しておく
		if(soy2_strpos($requestUri, "?") >= 0){
			$requestUri = substr($requestUri, 0, strpos($requestUri, "?"));
		}

		//PATH_INFO
		$pathInfo = self::_getPathInfo();
		
		//先頭はスラッシュ
		if(strlen($pathInfo) && $pathInfo[0] !== "/"){
			$pathInfo = "/" . $pathInfo;
		}
		
		//リダイレクトループを止める
		if(self::_checkLoop($pathInfo)){
			return false;
		}
		
		//プレフィックスを取得(スマホ版も考慮)
		$prefix = self::_getPrefix();
		
		//サイトID：最初と最後に/を付けておく
		$siteDir = strlen($pathInfo) ? strtr(rawurldecode($requestUri), array($pathInfo => "")) : $requestUri;//strtrのキーは空文字列であってはいけない
		
		//最初と最後に/を付けておく
		if(soy2_strpos($siteDir, "/") > 0){
			$siteDir = "/" . $siteDir;
		}
		if(substr($siteDir, -1) !== "/"){
			$siteDir = $siteDir . "/";
		}
		
		//URLエンコードされたPATH_INFOを取得する
		$pathInfo = str_replace("%2F", "/", rawurlencode($pathInfo));
		$pathInfo = str_replace("%3F", "?", $pathInfo);
		$pathInfo = str_replace("%3D", "=", $pathInfo);
		
		//prefixが0文字の場合はpathInfoの値から他のprefixがないかを調べる。もしくは他の言語域でないかも調べる
		if(strlen($prefix) === 0 || $prefix === self::_getCarrierPrefix() || self::_checkPathInfo($pathInfo)){
			$pathInfo = self::_removeInsertPrefix($pathInfo);
		}

		//prefixを付ける
		return self::_addQueryString(self::_convertPath($siteDir, $prefix, $pathInfo));
	}
	
	/**
	 * リダイレクトを行う必要があるか調べる
	 * @param string
	 * @return bool
	 */
	function checkRedirectPath(string $path=""){
		if(!$path) return false;
		
		$path = self::_formatPath($path);
		$requestUri = self::_formatPath($_SERVER["REQUEST_URI"]);
		
		return ($path !== $requestUri);
	}
	
	/**
	 * 無限ループになるかチェック
	 * @param string
	 * @return bool
	 */
	private function _checkLoop(string $path){
		$prefix = $prefix = self::_getLanguagePrefix();
		
		if((self::_isMobile() || self::_isSmartPhone()) && strlen(self::_getCarrierPrefix())){
			$path = str_replace("/" . SOYCMS_CARRIER_PREFIX, "", $path);
		}
		return ($path === "/" . $prefix || soy2_strpos($path, "/" . $prefix . "/") === 0 );
	}
	
	/**
	 * 他の言語域のプレフィックスがPathに入っていないか？
	 * @param string
	 * @return bool
	 */
	private function _checkPathInfo(string $pathInfo){
//		if(isset($cnf["check_browser_language_config"])) unset($cnf["check_browser_language_config"]);
//		if(isset($cnf[SOYSHOP_PUBLISH_LANGUAGE])) unset($cnf[SOYSHOP_PUBLISH_LANGUAGE]);
		if(!count($this->config)) return false;
		
		//スマホのプレフィックスを除く
		if(strpos($pathInfo, "/" . self::_getCarrierPrefix() . "/") === 0){
			$pathInfo = str_replace("/" . self::_getCarrierPrefix() . "/", "/", $pathInfo);
		}
		
		foreach($this->config as $lang => $conf){
			if($conf["is_use"] == SOYCMSUtilMultiLanguageUtil::IS_USE && strlen($conf["prefix"])){
				
				if(strpos($pathInfo, "/" . $conf["prefix"] . "/") === 0 || $pathInfo == "/" . $conf["prefix"]){
					return true;
				}
			}
		}
		
		return false;
	}
	
	/**
	 * @param string
	 * @return string
	 */
	private function _removeInsertPrefix(string $path){
		//スマホ分のプレフィックスを先に削除
		if((self::_isMobile() || self::_isSmartPhone()) && strlen(self::_getCarrierPrefix())){
			$path = str_replace("/" . SOYCMS_CARRIER_PREFIX, "", $path);
		}
		if(!count($this->config)) return $path;

		foreach($this->config as $conf){
			if(!isset($conf["prefix"])) continue;
			if(preg_match('/\/' . $conf["prefix"] . '\//', $path) || $path == "/" . $conf["prefix"]){
				$path = str_replace("/" . $conf["prefix"], "", $path);
			}
		}
		return $path;
	}
	
	/**
	 * @param string, string, string
	 * @return string
	 */
	private function _convertPath(string $siteDir, string $prefix, string $pathInfo){
		//パスの結合を行う前にpathInfoからスマホプレフィックスを除く
		if((self::_isMobile() || self::_isSmartPhone()) && strlen(self::_getCarrierPrefix())){
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
	
	/**
	 * @param string
	 * @return string
	 */
	private function _formatPath(string $path){
		if(soy2_strpos($path, "/") > 0) $path = "/" . $path;
		if(soy2_strpos($path, "/?") > 0) $path = str_replace("/?", "?", $path);

		// GETパラメータがuriと同一の場合はGETパラメータを外しておく
		if(soy2_strpos($path, "?") > 0){
			$_arr = explode("?", $path);
			if(count($_arr) === 2 && trim($_arr[0], "/") === $_arr[1]){
				$path = substr($path, 0, soy2_strpos($path, "?"));
			}
		}
		
		return $path;		
	}
	
	/**
	 * @return string
	 */
	private function _getPrefix(){
		$prefix = self::_getLanguagePrefix();
		
		//ガラケーページもしくはスマホページを見ている場合、requestUriにスマホページ分も考慮する
		if((self::_isMobile() || self::_isSmartPhone())){
			if(strlen($prefix) && strlen(self::_getCarrierPrefix())){
				$prefix = SOYCMS_CARRIER_PREFIX . "/" . $prefix;
			}else{
				$prefix = SOYCMS_CARRIER_PREFIX;
			}
		}
		
		return $prefix;
	}
	
	/**
	 * @paran string
	 * @return string
	 */
	private function _addQueryString(string $path){
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
					if(is_null($value)) continue;
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

		// サーバによっては?language=*が複数回付与されてしまうことがある
		if(substr_count($path, "?") >= 2){
			for(;;){
				if(substr_count($path, "?") === 1) break;
				$path = substr($path, 0, strrpos($path, "?"));
			}
		}
		
		// サーバによってはpathの末尾に?が付いてしまうことがある
		return rtrim($path, "?");
	}
	
	/**
	 * PATH_INFOを取得する
	 * PATH_INFOをGETで渡す環境があるらしい
	 */
	private function _getPathInfo(){
		if(isset($_SERVER['PATH_INFO'])){
			return $_SERVER['PATH_INFO'];
		}elseif(isset($_GET['pathinfo'])){
			return $_GET['pathinfo'];
		}else{
			return "";
		}
	}
	
	private function _isMobile(){
		return (defined("SOYCMS_IS_MOBILE") && SOYCMS_IS_MOBILE);
	}
	
	private function _isSmartPhone(){
		return (defined("SOYCMS_IS_SMARTPHONE") && SOYCMS_IS_SMARTPHONE);
	}
	
	private function _getCarrierPrefix(){
		return (defined("SOYCMS_CARRIER_PREFIX") && strlen(SOYCMS_CARRIER_PREFIX)) ? SOYCMS_CARRIER_PREFIX : null;
	}
	
	private function _getLanguagePrefix(){
		$cnf = $this->config;
		if(!isset($cnf[SOYCMS_PUBLISH_LANGUAGE]["is_use"])) return "";
		if($cnf[SOYCMS_PUBLISH_LANGUAGE]["is_use"] == SOYCMSUtilMultiLanguageUtil::NO_USE) return "";
		
		return (isset($cnf[SOYCMS_PUBLISH_LANGUAGE]["prefix"])) ? $cnf[SOYCMS_PUBLISH_LANGUAGE]["prefix"] : "";
		
	}

	function setConfig(array $config){
		$this->config = $config;
	}
}