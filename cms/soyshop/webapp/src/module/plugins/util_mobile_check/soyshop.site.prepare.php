<?php
class MobileCheckPrepareAction extends SOYShopSitePrepareAction{

	//スマートフォンの転送先設定用定数
	const CONFIG_SP_REDIRECT_PC = 0;//PCサイト
	const CONFIG_SP_REDIRECT_SP = 1;//スマートフォンサイト
	const CONFIG_SP_REDIRECT_MB = 2;//ケータイサイト

	const REDIRECT_PC = 0;//PCサイト表示（何もしない）
	const REDIRECT_SP = 1;//スマートフォンサイト転送
	const REDIRECT_MB = 2;//ケータイサイト転送

	private $config;
	
	private $carrier;

	/**
	 * @return string
	 */
	function prepare(){

		SOY2::import("module.plugins.util_mobile_check.util.UtilMobileCheckUtil");

		$redirect = self::REDIRECT_PC;
		$isMobile = false;
		$isSmartPhone = false;

		//二度実行しない
		if(defined("SOYSHOP_IS_MOBILE") || defined("SOYSHOP_IS_SMARTPHONE")){
			return;
		}

		$this->config = UtilMobileCheckUtil::getConfig();
		$config = $this->config;


		//クッキー非対応機種の設定
		define("SOYSHOP_COOKIE", ( isset($config["cookie"]) && $config["cookie"] == 1) );


		//セッションIDを再生成しておく（DoCoMo i-mode1.0 限定）
		if(
			self::isMobile() && defined("SOYSHOP_MOBILE_CARRIER") && SOYSHOP_MOBILE_CARRIER == "DoCoMo" && SOYSHOP_COOKIE
			&&
			(isset($_GET[session_name()]) || isset($_POST[session_name()])) && !isset($_COOKIE[session_name()])
		){

			$session_time = $config["session"] * 60;

			ini_set("session.gc_maxlifetime", $session_time);

			if(isset($_POST[session_name()])){
				session_id($_POST[session_name()]);
			}else{
				session_id($_GET[session_name()]);
			}

			session_start();
			session_regenerate_id(true);
			output_add_rewrite_var(session_name(), session_id());
		}

		//ケータイ
		if(self::isMobile()){
			$redirect = self::REDIRECT_MB;
			$isMobile = true;
		}

		//iPad
		if(!$isMobile && self::isTablet()){
			if($config["redirect_ipad"] == self::CONFIG_SP_REDIRECT_SP){
				$redirect = self::REDIRECT_SP;
				$isSmartPhone = true;
			}else{
				//PC
				$redirect = self::REDIRECT_PC;
			}
		}

		//スマートフォン(iPadだった場合はチェックしない)
		if(!$isMobile && (!defined("SOYSHOP_IS_TABLET") || SOYSHOP_IS_TABLET === false) && self::isSmartPhone()){
			if($config["redirect_iphone"] == self::CONFIG_SP_REDIRECT_SP){
				$redirect = self::REDIRECT_SP;
				$isSmartPhone = true;
			}elseif($config["redirect_iphone"] == self::CONFIG_SP_REDIRECT_MB){
				//ケータイ
				$redirect = self::REDIRECT_MB;
				$isMobile = true;
			}else{
				//PC
				$redirect = self::REDIRECT_PC;
			}
		}
		
		//ここで一旦定義を行う
		if(!defined("SOYSHOP_IS_MOBILE")){
			define("SOYSHOP_IS_MOBILE", $isMobile);
		}
		
		if(!defined("SOYSHOP_IS_SMARTPHONE")){
			define("SOYSHOP_IS_SMARTPHONE", $isSmartPhone);
		}
		
		//別キャリアを見ている場合は一旦PCにとばす。
		if(SOYSHOP_IS_MOBILE || SOYSHOP_IS_SMARTPHONE){
			//モバイルとスマホで同じプレフィックスを設定するとリダイレクトループになるので、別の端末でも同じ端末として解釈
			if($this->config["prefix"] != $this->config["prefix_i"]){
				$redirectPrefix = ($redirect == self::REDIRECT_MB) ? $this->config["prefix_i"] : $this->config["prefix"];
				self::checkCarrier($redirectPrefix);
			}
		}
		
		//PCの場合以外のリダイレクト処理
		if($redirect != self::REDIRECT_PC){
			//prefixの決定
			if($redirect == self::REDIRECT_MB && strlen($config["prefix"])){
				$prefix = $config["prefix"];
				define("SOYSHOP_DOCOMO_CSS", $config["css"]);
				//カートのお買物に戻るリンクの設定
				define("SOYSHOP_RETURN_LINK", $config["url"]);

				//au用の設定
				if(defined("SOYSHOP_MOBILE_CARRIER") && SOYSHOP_MOBILE_CARRIER == "KDDI"){
					header("Pragma: no-cache");
					header("Cache-Control: no-cache");
					header("Expires: -1");
				}
			}
			if($redirect == self::REDIRECT_SP && strlen($config["prefix_i"])){
				$prefix = $config["prefix_i"];
			}

			//リダイレクト先の絶対パス
			$path = self::getRedirectPath($prefix);
			
			if($path){
				//if do not work Location header
				ob_start();
				echo "<a href=\"" . htmlspecialchars($path, ENT_QUOTES, "UTF-8") . "\">" . htmlspecialchars($config["message"], ENT_QUOTES, "UTF-8") . "</a>";

				//リダイレクト
				if($config["redirect"]) SOY2PageController::redirect($path);

				exit;
			}
						
		//PCの場合、念の為、別キャリアのページを見ていないか調べる
		}else{
			self::checkCarrier($this->config["prefix"]);
			self::checkCarrier($this->config["prefix_i"]);
			
			//PC版の場合はprefixはなし
			$prefix = null;
		}
		
		//リダイレクトをしなかった場合、prefixを定数に入れておく
		if(!defined("SOYSHOP_CARRIER_PREFIX")) define("SOYSHOP_CARRIER_PREFIX", $prefix);
	}

	private function isMobile(){
		$isMobile = false;

		$isMobile = self::checkAccessFromMobile();
		$carrier = $this->carrier;

		if(!defined("SOYSHOP_MOBILE_CARRIER")){
			define("SOYSHOP_MOBILE_CARRIER", $carrier);
		}

		return $isMobile;
	}
	
	/**
	 * iPadからのアクセスかどうか
	 */
	private function isTablet(){
		$isTablet = self::checkAccessFromTablet();		
		
		if(!defined("SOYSHOP_IS_TABLET")){
			define("SOYSHOP_IS_TABLET", $isTablet);
		}
		
		//iPadだった場合、スマホの設定を見ないことにする
		if($isTablet){
			if($this->config["redirect_ipad"] == self::CONFIG_SP_REDIRECT_SP){
				$isSmartPhone = true;
			}else{
				$isSmartPhone = false;
			}
			
			if(!defined("SOYSHOP_IS_SMARTPHONE")){
				define("SOYSHOP_IS_SMARTPHONE", $isSmartPhone);
			}	
		}
		
		return $isTablet;
	}

	/**
	 * スマートフォンからのアクセスかどうか
	 */
	private function isSmartPhone(){
		$isSmartPhone = false;

		if(
			$this->config["redirect_iphone"] == self::CONFIG_SP_REDIRECT_SP || 
			$this->config["redirect_iphone"] == self::CONFIG_SP_REDIRECT_MB
		){
	
			$isSmartPhone = self::checkAccessFromSmartphone();
		}	
		
		return $isSmartPhone;
	}
	
	//モバイルからのアクセスであるか
	private function checkAccessFromMobile(){
		$agent = (isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : "";

		//DoCoMo MOVA
		if(preg_match("/^DoCoMo\/1.0/i", $agent)){
		   $isMobile = true;
		   $this->carrier = "DoCoMo";

		//DoCoMo FOMA
		}elseif(preg_match("/^DoCoMo\/2.0/i", $agent)){
			$isMobile = true;
			//i-modeブラウザ2.0かチェック
			if(strpos($agent,"c500") !== false){
				$this->carrier = "i-mode2.0";
			}else{
				$this->carrier = "DoCoMo";
			}

		//SoftBank
		}elseif(preg_match("/^(J-PHONE|Vodafone|MOT-[CV]|SoftBank)/i", $agent)){
		   $isMobile = true;
		   $this->carrier = "SoftBank";

		//au
		}elseif(preg_match("/^KDDI-/i", $agent) || preg_match("/UP\.Browser/i", $agent)){
		   $isMobile = true;
		   $this->carrier = "KDDI";

		//それ以外はスルー
		}else{
			$isMobile = false;
			$this->carrier = "PC";
		}
		
		return $isMobile;
	}
	
	//Tabletからであるかチェック
	private function checkAccessFromTablet(){
		$agent = (isset($_SERVER['HTTP_USER_AGENT'])) ? mb_strtolower($_SERVER['HTTP_USER_AGENT']) : "";
		
		if(strpos($agent, "ipad") !== false){
			return true;
		}elseif(strpos($agent, "windows") !== false && strpos($agent, "touch") !== false){
			return true;
		}elseif(strpos($agent, "android") !== false && strpos($agent, "mobile") === false){
			return true;
		}elseif(strpos($agent, "firefox") !== false && strpos($agent, "tablet") !== false){
			return true;
		}elseif(strpos($agent, "kindle") !== false || strpos($agent, "silk") !== false){
			return true;
		}elseif(strpos($agent, "playbook") !== false){
			return true;
		}else{
			return false;
		}
	}
	
	//スマホからであるかチェック
	private function checkAccessFromSmartphone(){
		$agent = (isset($_SERVER['HTTP_USER_AGENT'])) ? mb_strtolower($_SERVER['HTTP_USER_AGENT']) : "";
		
		if(strpos($agent, "iphone") !== false){
			return true;
		}elseif(strpos($agent, "ipod") !== false){
			return true;
		}elseif(strpos($agent, "android") !== false && strpos($agent, "mobile") !== false){
			return true;
		}elseif(strpos($agent, "windows") !== false && strpos($agent, "phone") !== false){
			return true;
		}elseif(strpos($agent, "firefox") !== false && strpos($agent, "mobile") !== false){
			return true;
		}elseif(strpos($agent, "blackberry") !== false){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * キャリア判定でパスとキャリアが間違っている時、
	 * 一旦PCサイトにリダイレクトさせてから、サイドキャリアに対応したサイトにリダイレクト
	 */
	private function checkCarrier($prefix){
		//PATH_INFO
		$pathInfo = self::getPathInfo();
		
		if($pathInfo === "/" . $prefix || strpos($pathInfo, "/" . $prefix . "/") === 0){
			$path = self::getRedirectPcPath($prefix);
			SOY2PageController::redirect($path);
			exit;
		}
	}

	/**
	 * URLにプレフィックスを付けた絶対パスを返す
	 */
	private function getRedirectPath($prefix){
		//REQUEST_URI
		$requestUri = rawurldecode($_SERVER['REQUEST_URI']);
		//getの値を取り除く
		if(strpos($requestUri, "?") !== false){
			$requestUri = substr($requestUri, 0, strpos($requestUri, "?"));
		}
		
		//スマホのプレフィックスと多言語のプレフィックスが付与されている場合はfalseを返す
		if(self::checkMultiLanguage($requestUri)){
			return false;
		}

		//PATH_INFO
		$pathInfo = self::getPathInfo();
		
		//先頭はスラッシュ
		if(strlen($pathInfo) && $pathInfo[0] !== "/"){
			$pathInfo = "/" . $pathInfo;
		}
		
		//無限ループになるときはfalseを返す
		if(self::checkLoop($pathInfo, $prefix)){
			return false;
		}

		//サイトID：最初と最後に/を付けておく
		$siteDir = strlen($pathInfo) ? strtr($requestUri, array($pathInfo => "")) : $requestUri ;//strtrのキーは空文字列であってはいけない
		if($siteDir[0] !== "/"){
			$siteDir = "/" . $siteDir;
		}
		if(substr($siteDir, -1) !== "/"){
			$siteDir = $siteDir . "/";
		}

		//prefixを付ける
		$path = $siteDir. $prefix . $pathInfo;

		return self::addQueryString($path);
	}
	
	//多言語化プラグインがすでに実行されているか調べる
	private function checkMultiLanguage($path){
		$reg = "/" . $this->config["prefix_i"] . "/";
		SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");
		$config = UtilMultiLanguageUtil::getConfig();
		if(isset($config["check_browser_language_config"])) unset($config["check_browser_language_config"]);
		foreach($config as $conf){
			if(!isset($conf["prefix"]) || strlen($conf["prefix"]) === 0) continue;
			if(strpos($path, $reg . $conf["prefix"]) === 0) return true;
		}
		
		return false;
	}
	
	private function checkLoop($path, $prefix){
		return ( $path === "/" . $prefix || strpos($path, "/" . $prefix . "/") === 0 );
	}

	/**
	 * 各キャリアのprefixを除いたパスを返す
	 */
	private function getRedirectPcPath($prefix){
		//REQUEST_URI
		$requestUri = rawurldecode($_SERVER['REQUEST_URI']);

		//getの値を取り除く
		if(strpos($requestUri,"?") !== false){
			$requestUri = substr($requestUri, 0, strpos($requestUri, "?"));
		}

		//PATH_INFO
		$pathInfo = self::getPathInfo();
		
		//先頭はスラッシュ
		if(strlen($pathInfo) && $pathInfo[0] !== "/"){
			$pathInfo = "/" . $pathInfo;
		}
		
		//サイトID：最初と最後に/を付けておく
		$path = $requestUri;
		if($path[0] !== "/"){
			$path = "/" . $path;
		}
		if(substr($path, -1) !== "/"){
			$path = $path . "/";
		}

		//各キャリアのprefixを除いたものを返す
		if(strrpos($path,"/" . $prefix) == strlen($path) - strlen($prefix) - 1){
			$path = str_replace("/" . $prefix, "", $path);
		}else{
			$path = str_replace("/" . $prefix . "/", "/", $path);
		}

		return self::addQueryString($path);
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
}

SOYShopPlugin::extension("soyshop.site.prepare", "util_mobile_check", "MobileCheckPrepareAction");