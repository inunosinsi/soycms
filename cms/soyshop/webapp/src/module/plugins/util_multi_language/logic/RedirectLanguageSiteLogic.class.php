<?php

class RedirectLanguageSiteLogic extends SOY2LogicBase{

	function __construct(){
		SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");
	}

	//カートページかマイページを表示している時
	function defineApplicationId($config){
		if(isset($config[SOYSHOP_PUBLISH_LANGUAGE]) && strlen($config[SOYSHOP_PUBLISH_LANGUAGE]["prefix"]) > 0){
			$cartId = soyshop_get_cart_id() . "_" . $config[SOYSHOP_PUBLISH_LANGUAGE]["prefix"];
			$mypageId = soyshop_get_mypage_id() . "_" . $config[SOYSHOP_PUBLISH_LANGUAGE]["prefix"];

			define("SOYSHOP_CURRENT_CART_ID", $cartId);
			define("SOYSHOP_CURRENT_MYPAGE_ID", $mypageId);
		}
	}

	function getLanguageArterCheck($config){
		$lang = trim($_GET["language"]);
		$language = UtilMultiLanguageUtil::LANGUAGE_JP;

		foreach($config as $key => $conf){
			if($conf["is_use"] == UtilMultiLanguageUtil::IS_USE && $lang == $key){
				$language = $lang;
				break;
			//prefixの方でも調べてみる
			}else if(strlen($conf["prefix"]) && $lang == $conf["prefix"]){
				$language = $key;
				break;
			}

		}

		return $language;
	}

	function getJapanaseUrl($config, $url){
		$url = trim($url);
		$lang = SOY2ActionSession::getUserSession()->getAttribute("soyshop_publish_language");
		if(is_null($lang)) $lang = UtilMultiLanguageUtil::LANGUAGE_JP;

		if(!isset($config[$lang]) || !strlen($config[$lang]["prefix"])) return $url;
		return str_replace("/" . $config[$lang]["prefix"] . "/", "/", $url);
	}

	function getRedirectPath($config){

		//REQUEST_URI
		$requestUri = rawurldecode($_SERVER['REQUEST_URI']);

		//getの値を取り除く
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
		$siteDir = strlen($pathInfo) ? strtr($requestUri, array($pathInfo => "")) : $requestUri ;//strtrのキーは空文字列であってはいけない
		if(strlen($siteDir) && $siteDir[0] !== "/"){
			$siteDir = "/" . $siteDir;
		}
		if(substr($siteDir, -1) !== "/" && strlen($prefix)){
			$siteDir = $siteDir . "/";
		}

		//URLエンコードされたPATH_INFOを取得する
		$pathInfo = str_replace("%2F", "/", rawurlencode($pathInfo));

		//prefixが0文字の場合はpathInfoの値から他のprefixがないかを調べる。もしくは他の言語域でないかも調べる
		if(strlen($prefix) === 0 || $prefix === self::getCarrierPrefix() || self::checkPathInfo($pathInfo, $config)){
			$pathInfo = self::removeInsertedPrefix($pathInfo, $config);
		}

		//prefixを付ける
		$path = self::convertPath($siteDir, $prefix, $pathInfo);

		return self::addQueryString($path);
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
		$prefix = self::getLanguagePrefix($config);
		if((self::isMobile() || self::isSmartPhone()) && strlen(self::getCarrierPrefix())){
			$path = str_replace("/" . SOYSHOP_CARRIER_PREFIX, "", $path);
		}
		return ($path === "/" . $prefix || strpos($path, "/" . $prefix . "/") === 0 );
	}

	//他の言語域のプレフィックスがPathに入っていないか？
	private function checkPathInfo($pathInfo, $config){
		if(isset($config["check_browser_language_config"])) unset($config["check_browser_language_config"]);
		if(isset($config[SOYSHOP_PUBLISH_LANGUAGE])) unset($config[SOYSHOP_PUBLISH_LANGUAGE]);

		//スマホのプレフィックスを除く
		if(strpos($pathInfo, "/" . self::getCarrierPrefix() . "/") === 0){
			$pathInfo = str_replace("/" . self::getCarrierPrefix() . "/", "/", $pathInfo);
		}

		foreach($config as $lang => $conf){
			if(!isset($conf["is_use"]) || !is_numeric($conf["is_use"])) continue;
			if(!isset($conf["prefix"]) || !is_string($conf["prefix"])) continue;
			if($conf["is_use"] == UtilMultiLanguageUtil::IS_USE && strlen($conf["prefix"])){

				if(strpos($pathInfo, "/" . $conf["prefix"] . "/") === 0 || $pathInfo == "/" . $conf["prefix"]){
					return true;
				}
			}
		}

		return false;
	}

	private function removeInsertedPrefix($path, $config){
		//スマホ分のプレフィックスを先に削除
		if((self::isMobile() || self::isSmartPhone()) && strlen(self::getCarrierPrefix())){
			$path = str_replace("/" . SOYSHOP_CARRIER_PREFIX, "", $path);
		}

		if(isset($config["check_browser_language_config"])) unset($config["check_browser_language_config"]);
		foreach($config as $conf){
			if(!isset($conf["is_use"]) || $conf["is_use"] == UtilMultiLanguageUtil::NO_USE) continue;
			if(!isset($conf["prefix"]) || strlen($conf["prefix"]) === 0) continue;
			if(preg_match('/\/' . $conf["prefix"] . '\//', $path) || $path == "/" . $conf["prefix"]){
				$path = str_replace("/" . $conf["prefix"], "", $path);
			}
		}

		return $path;
	}

	//パスの整形周りの処理
	private function convertPath($siteDir, $prefix, $pathInfo){
		//パスの結合を行う前にpathInfoからスマホプレフィックスを除く
		if((self::isMobile() || self::isSmartPhone()) && strlen(self::getCarrierPrefix())){
			if($pathInfo === "/" . SOYSHOP_CARRIER_PREFIX || strpos($pathInfo, "/" . SOYSHOP_CARRIER_PREFIX . "/") === 0){
				$pathInfo = str_replace("/" . SOYSHOP_CARRIER_PREFIX, "", $pathInfo);
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

		//スマホページもしくはガラケーページを見ている場合、requestUriにスマホページ分も考慮する
		if((self::isMobile() || self::isSmartPhone())){
			if(strlen($prefix) && strlen(self::getCarrierPrefix())){
				$prefix = SOYSHOP_CARRIER_PREFIX . "/" . $prefix;
			}else{
				$prefix = SOYSHOP_CARRIER_PREFIX;
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
		return (defined("SOYSHOP_IS_MOBILE") && SOYSHOP_IS_MOBILE);
	}

	private function isSmartPhone(){
		return (defined("SOYSHOP_IS_SMARTPHONE") && SOYSHOP_IS_SMARTPHONE);
	}

	private function getCarrierPrefix(){
		return (defined("SOYSHOP_CARRIER_PREFIX") && strlen(SOYSHOP_CARRIER_PREFIX)) ? SOYSHOP_CARRIER_PREFIX : null;
	}

	private function getLanguagePrefix($config){
		if(!isset($config[SOYSHOP_PUBLISH_LANGUAGE]["is_use"])) return "";
		if($config[SOYSHOP_PUBLISH_LANGUAGE]["is_use"] == UtilMultiLanguageUtil::NO_USE) return "";

		return (isset($config[SOYSHOP_PUBLISH_LANGUAGE]["prefix"])) ? $config[SOYSHOP_PUBLISH_LANGUAGE]["prefix"] : "";

	}
}
