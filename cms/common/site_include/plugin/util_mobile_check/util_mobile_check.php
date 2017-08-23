<?php
/*
 * Created on 2009/12/02
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

UtilMobileCheckPlugin::register();

class UtilMobileCheckPlugin{

	const PLUGIN_ID = "UtilMobileCheckPlugin";

	//スマートフォンの転送先設定用定数
	const CONFIG_SP_REDIRECT_PC = 0;//PCサイト
	const CONFIG_SP_REDIRECT_SP = 1;//スマートフォンサイト
	const CONFIG_SP_REDIRECT_MB = 2;//ケータイサイト

	const REDIRECT_PC = 0;//PCサイト表示（何もしない）
	const REDIRECT_SP = 1;//スマートフォンサイト転送
	const REDIRECT_MB = 2;//ケータイサイト転送

	/**
	 * 設定
	 */
	public $prefix      = "m";//モバイルのURIプレフィックス
	public $smartPrefix = "i";//スマートフォンのURIプレフィックス
	public $redirect = false;//自動転送機能を有効にするかどうか
	public $message = "Go to mobile page.";//転送文言
	public $redirectIphone = self::CONFIG_SP_REDIRECT_SP;//スマートフォンでの転送先
	public $redirectIpad = self::CONFIG_SP_REDIRECT_SP;//iPadでの転送先

	private $config;

	private $carrier;


	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name"=>"携帯自動振り分けプラグイン",
			"description"=>"携帯電話やスマートフォンでのアクセス時に対応したページに転送します。",
			"author"=>"株式会社Brassica",
			"url"=>"https://brassica.jp/",
			"mail"=>"soycms@soycms.net",
			"version"=>"0.9.2"
		));
		CMSPlugin::addPluginConfigPage(self::PLUGIN_ID, array(
			$this, "config_page"
		));

		//二回目以降の動作
		if(CMSPlugin::activeCheck($this->getId())){

			//公開側へのアクセス時に必要に応じてリダイレクトする
			//出力前にセッションIDをURLに仕込むための宣言をしておく
			CMSPlugin::setEvent('onSiteAccess', $this->getId(), array($this, "onSiteAccess"));

/*
			//公開側へのアクセス時に必要に応じてリダイレクトする
			CMSPlugin::setEvent('onSiteAccess',$this->getId(),array($this,"redirect"));

			//出力前にセッションIDをURLに仕込むための宣言をしておく
			CMSPlugin::setEvent('onOutput',$this->getId(),array($this,"addSessionVar"));
*/
		//プラグインの初回動作
		}else{
			//
		}
	}

	/**
	 *
	 * @return $html
	 */
	function config_page($message){
		$form = SOY2HTMLFactory::createInstance("UtilMobileCheckPluginConfigFormPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * サイトアクセス時の動作
	 */
	function onSiteAccess($obj){
		$this->redirect($obj);
		self::addSessionVar();
	}

	/**
	 * 公開側の出力
	 */
	function redirect(){
		$config = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		$this->config = $config;

		$redirect = self::REDIRECT_PC;
		$isMobile = false;
		$isSmartPhone = false;

		//ケータイ
		if(self::isMobile()){
			$redirect = self::REDIRECT_MB;
			$isMobile = true;
		}

		//iPad
		if(!$isMobile && self::isTablet()){
			if($config->redirectIpad == self::CONFIG_SP_REDIRECT_SP){
				$redirect = self::REDIRECT_SP;
				$isSmartPhone = true;
			}else{
				//PC
				$redirect = self::REDIRECT_PC;
			}
		}

		//スマートフォン
		if(!$isMobile && (!defined("SOYCMS_IS_TABLET") || !SOYCMS_IS_TABLET) && self::isSmartPhone()){
			if($config->redirectIphone == self::CONFIG_SP_REDIRECT_SP){
				$redirect = self::REDIRECT_SP;
				$isSmartPhone = true;
			}elseif($config->redirectIphone == self::CONFIG_SP_REDIRECT_MB){
				//ケータイ
				$redirect = self::REDIRECT_MB;
				$isMobile = true;
			}else{
				//PC
				$redirect = self::REDIRECT_PC;
			}
		}

		//ここで定義を開始する
		if(!defined("SOYCMS_IS_MOBILE")){
			define("SOYCMS_IS_MOBILE", $isMobile);
		}

		if(!defined("SOYCMS_IS_SMARTPHONE")){
			define("SOYCMS_IS_SMARTPHONE", $isSmartPhone);
		}

		//別キャリアを見ている場合は一旦PCにとばす。
		if(SOYCMS_IS_MOBILE || SOYCMS_IS_SMARTPHONE){
			//モバイルとスマホで同じプレフィックスを設定するとリダイレクトループになるので、別の端末でも同じ端末として解釈
			if($config->prefix != $config->smartPrefix){
				$redirectPrefix = ($redirect == self::REDIRECT_MB) ? $config->smartPrefix : $config->prefix;
				self::checkCarrier($redirectPrefix);
			}
		}

		//PCの場合以外のリダイレクト処理
		if($redirect != self::REDIRECT_PC){
			//prefixの決定
			if($redirect == self::REDIRECT_MB && strlen($config->prefix)){
				$prefix = $config->prefix;
			}
			if($redirect == self::REDIRECT_SP && strlen($config->smartPrefix)){
				$prefix = $config->smartPrefix;
			}

			//リダイレクト先の絶対パス
			$path = self::getRedirectPath($prefix);

			if($path){

				//if do not work Location header
				//CMSPageController::redirect()の中でexitをしているので、あらかじめ出力バッファーに入れておく必要がある。
				ob_start();
				echo "<a href=\"" . htmlspecialchars($path, ENT_QUOTES, "UTF-8") . "\">" . htmlspecialchars($config->message, ENT_QUOTES, "UTF-8") . "</a>";

				//リダイレクト
				if($config->redirect){
					CMSPageController::redirect($path);
				}
				exit;
			}

		//PCの場合、念の為、別キャリアのページを見ていないか調べる
		}else{
			self::checkCarrier($config->prefix);
			self::checkCarrier($config->smartPrefix);

			//PC版の場合はprefixはなし
			$prefix = null;
		}

		//リダイレクトをしなかった場合、prefixを定数に入れておく
		if(!defined("SOYCMS_CARRIER_PREFIX")) define("SOYCMS_CARRIER_PREFIX", $prefix);
	}

	/**
	 * output_add_rewrite_varを使ってリンクのURLにセッションIDを付ける
	 */
	private function addSessionVar(){
		if(
			self::isMobile() && SOYCMS_MOBILE_CARRIER == "DoCoMo"
			&&
			(isset($_GET[session_name()]) || isset($_POST[session_name()])) && !isset($_COOKIE[session_name()])
		){
			session_regenerate_id(true);
			output_add_rewrite_var(session_name(), session_id());
		}
		return null;
	}

	/**
	 * ケータイからのアクセスかどうか
	 */
	private function isMobile(){

		$isMobile = self::checkAccessFromMobile();
		$carrier = $this->carrier;

		if(!defined("SOYCMS_MOBILE_CARRIER")){
			define("SOYCMS_MOBILE_CARRIER", $carrier);
		}

		return $isMobile;
	}

	/**
	 * iPadからのアクセスかどうか
	 */
	private function isTablet(){
		$isTablet = self::checkAccessFromTablet();

		if(!defined("SOYCMS_IS_TABLET")){
			define("SOYCMS_IS_TABLET", $isTablet);
		}

		//iPadだった場合、スマホの設定を見ないことにする
		if($isTablet){
			if($this->config->redirectIpad == self::CONFIG_SP_REDIRECT_SP){
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
			$this->config->redirectIphone == self::CONFIG_SP_REDIRECT_SP ||
			$this->config->redirectIphone == self::CONFIG_SP_REDIRECT_MB
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

	//iPadからであるかチェック
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

	//多言語化プラグインがすでに実行されているか調べる
	private function checkMultiLanguage($path){
		$reg = "/" . $this->smartPrefix . "/";
		include_once(dirname(dirname(__FILE__)) . "/util_multi_language/util_multi_language.php");
		$obj = CMSPlugin::loadPluginConfig("UtilMultiLanguagePlugin");
		if(is_null($obj)){
			$obj = new UtilMultiLanguagePlugin();
		}
		$config = $obj->getConfig();
		if(isset($config["check_browser_language_config"])) unset($config["check_browser_language_config"]);
		foreach($config as $conf){
			if(!isset($conf["prefix"]) || strlen($conf["prefix"]) === 0) continue;
			if(strpos($path, $reg . $conf["prefix"]) === 0) return true;
		}

		return false;
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
		$requestUri = $_SERVER['REQUEST_URI'];
		//$_GETの値（QUERY_STRING）を削除しておく
		if(strpos($requestUri,"?") !== false){
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
		$siteDir = strlen($pathInfo) ? strtr(rawurldecode($requestUri), array($pathInfo => "")) : $requestUri ;//strtrのキーは空文字列であってはいけない
		//最初と最後に/を付けておく
		if(strpos($siteDir, "/") !== 0){
			$siteDir = "/" . $siteDir;
		}
		if(substr($siteDir, -1) !== "/"){
			$siteDir = $siteDir. "/";
		}

		//URLエンコードされたPATH_INFOを取るために、REQUEST_URIから作り直す
		$pathInfo = "/" . substr($requestUri, strlen($siteDir));

		//prefixを付ける
		$path = $siteDir. $prefix. $pathInfo;

		//絶対パスにQuery Stringを追加する
		if(isset($_SERVER["QUERY_STRING"]) && strlen($_SERVER["QUERY_STRING"]) > 0){

			//セッションIDが入っている場合にregenerateされている可能性があるので
			if(strpos($_SERVER["QUERY_STRING"], session_name()) !== false){
				$queries = explode("&", $_SERVER["QUERY_STRING"]);
				foreach($queries as $id => $item){
					if(strpos($item, session_name()) === 0){
						$queries[$id] = session_name() . "=" . session_id();
						break;
					}
				}
				$querystring = implode("&", $queries);
			}else{
				$querystring = $_SERVER["QUERY_STRING"];
			}


			$path .= "?" . $_SERVER["QUERY_STRING"];
		}

		return $path;
	}

	/**
	 * 各キャリアのprefixを除いたパスを返す
	 */
	private function getRedirectPcPath($prefix){
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

		//サイトID：最初と最後に/を付けておく
		$path = strlen($pathInfo) ? strtr($requestUri,array($pathInfo => "")) : $requestUri ;//strtrのキーは空文字列であってはいけない
		$path = $requestUri;
		if($path[0] !== "/"){
			$path = "/" . $path;
		}
		if(substr($path, -1) !== "/"){
			$path = $path. "/";
		}

		//各キャリアのprefixを除いたものを返す
		if(strrpos($path,"/" . $prefix) == strlen($path) - strlen($prefix) - 1){
			$path = str_replace("/" . $prefix, "", $path);
		}else{
			$path = str_replace("/" . $prefix . "/", "/", $path);
		}

		return self::addQueryString($path);;
	}

	private function checkLoop($path, $prefix){
		return ( $path === "/" . $prefix || strpos($path, "/" . $prefix . "/") === 0 );
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

	function getSmartPrefix(){
		return $this->smartPrefix;
	}
	function setSmartPrefix($smartPrefix){
		$this->smartPrefix = $smartPrefix;
	}
	function setPrefix($prefix){
		$this->prefix = $prefix;
	}
	function setRedirect($redirect){
		$this->redirect = $redirect;
	}
	function setMessage($message){
		$this->message = $message;
	}
	function setRedirectIphone($redirectIphone){
		$this->redirect_iphone = $redirectIphone;
	}
	function setRedirectIpad($redirectIpad){
		$this->redirectIpad = $redirectIpad;
	}

	public static function register(){
		include_once(dirname(__FILE__) . "/config.php");

		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(is_null($obj)){
			$obj = new UtilMobileCheckPlugin();
		}

		CMSPlugin::addPlugin(self::PLUGIN_ID,array($obj, "init"));
	}
}
