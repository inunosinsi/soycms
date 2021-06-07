<?php /* soy2_build 2018-12-27 04:49:07 */
/* SOY2/SOY2.php */
class SOY2{
	private $_rootDir = "webapp/";
	/**
	 * アプリケーションのディレクトリを設定（取得）
	 */
	public static function RootDir($dir = null){
		static $_static;
		if(!$_static)$_static = new SOY2();
		if($dir){
			if(substr($dir,strlen($dir)-1) != '/'){
				throw new Exception("[SOY2]RootDir must end by '/'.");
			}
			$_static->_rootDir = str_replace("\\", "/", $dir);
		}
		return $_static->_rootDir;
	}
	/**
	 * クラスのインポート
	 *
	 * @return クラス名
	 */
	public static function import($path,$extension =".class.php"){
		if(class_exists($path)){
			return $path;
		}
		$tmp = array();
		preg_match('/\.([a-zA-Z0-9_]+$)/',$path,$tmp);
		if(count($tmp)){
			$className = $tmp[1];
		}else{
			$className = $path;
		}
		$dir = self::RootDir();
		$path = str_replace(".","/",$path);
		if(is_readable($dir.$path.$extension) && include_once($dir.$path.$extension)){
			return $className;
		}else{
			return false;
		}
	}
	/**
	 * 指定ディレクトリにあるクラスを全てインポート
	 */
	public static function imports($dir, $rootDir = null){
		if(!$rootDir)$rootDir = SOY2::RootDir();
		$path = str_replace(".","/",$dir);
		$dirPath = $rootDir.str_replace("*","",$path);
		$files = scandir($dirPath);
		foreach($files as $file){
			if(preg_match('/.php$/',$file) && is_readable($dirPath.$file)){
				include_once($dirPath.$file);
			}
		}
	}
	/**
	 * オブジェクトのキャストを行う
	 *
	 * @uses SOY2::cast("クラス名",$obj);
	 * @uses SOY2::cast($obj2,$obj);
	 *
	 * キャスト先のオブジェクトはsetter必須
	 * キャスト元のオブジェクトはgetterがあればそちらを、無ければプロパティを直接。
	 * ただしプロパティがpublicでない場合はコピーしない（警告なし）
	 */
	public static function cast($className,$obj){
		if(!is_object($className)){
			if($className != "array" && $className != "object"){
				$result = self::import($className);
				if($result == false){
					throw new Exception("[SOY2]Could not find class:".$className);
				}
				$className = $result;
			}
		}
		$tmpObject = new stdClass;
		if($obj instanceof stdClass){
			$tmpObject = $obj;
		}else if(is_array($obj)){
			$tmpObject = (object)$obj;
		}else if(is_null($obj)){
			$tmpObject = new stdClass;
		}else{
			$refClass = new ReflectionClass($obj);
			$properties = $refClass->getProperties();
			foreach($properties as $property){
				$name = $property->getName();
				if($refClass->hasMethod("get".ucwords($name))){
					$method = "get".ucwords($name);
					$value = $obj->$method();
					if(is_string($value) && !strlen($value))$value = null;
					$tmpObject->$name = $value;
				}else{
					if(!$property->isPublic())continue;
					$value = $obj->$name;
					if(is_string($value) && !strlen($value))$value = null;
					$tmpObject->$name = $value;
				}
			}
		}
		if(is_object($className)){
			$newObj = $className;
		}else if($className == "array"){
			return (array)$tmpObject;
		}else if($className == "object"){
			return $tmpObject;
		}else{
			$newObj = new $className();
		}
		foreach($tmpObject as $prop => $property){
			if($newObj instanceof stdClass){
				$newObj->$prop = $property;
				continue;
			}
			$methodName = "set".ucwords($prop);
			if(!method_exists($newObj,$methodName))continue;
			$newObj->$methodName($property);
		}
		return $newObj;
	}
	/**
	 * SOY2フレームワークの全ての設定を1メソッドで。
	 *
	 * @example SOY2::config(
	 * 	array(
	 * 		"RootDir" => "webapp/",
	 * 		"ActionDir"  => "webapp/actions/",
	 * 		"PageDir"	=> "webapp/pages/",
	 * 		"CacheDir"	=> "page_cache/",
	 * 		"DaoDir"	=> "webapp/dao/",
	 * 		"EntityDir"	=> "webapp/entity/",
	 * 		"Dsn"		=> "localhost...",
	 * 		"user"		=> "",
	 * 		"pass"		=> ""
	 * ));
	 */
	public static function config($array){
		if(isset($array['RootDir'])){
			SOY2::RootDir($array['RootDir']);
		}
		if(isset($array['ActionDir'])){
			SOY2ActionConfig::ActionDir($array['ActionDir']);
		}
		if(isset($array['PageDir'])){
			SOY2HTMLConfig::PageDir($array['PageDir']);
		}
		if(isset($array['CacheDir'])){
			SOY2HTMLConfig::CacheDir($array['CacheDir']);
		}
		if(isset($array['DaoDir'])){
			SOY2DAOConfig::DaoDir($array['DaoDir']);
		}
		if(isset($array['EntityDir'])){
			SOY2DAOConfig::EntityDir($array['EntityDir']);
		}
		if(isset($array['Dsn'])){
			SOY2DAOConfig::Dsn($array['Dsn']);
		}
		if(isset($array['pass'])){
			SOY2DAOConfig::user($array['user']);
		}
		if(isset($array['pass'])){
			SOY2DAOConfig::pass($array['pass']);
		}
	}
}
/* SOY2/SOY2_Controller.class.php */
/**
 * @package SOY2.controller
 */
interface SOY2_Controller{
	public static function run();
}
/**
 * @package SOY2.controller
 */
interface SOY2_ClassPathBuilder{
	function getClassPath($path);
}
/**
 * @package SOY2.controller
 */
interface SOY2_PathBuilder{
	function getPath();
	function getArguments();
}
/* SOY2/class/SOY2ActionController.php */
/**
 * @package SOY2.controller
 *
 * mod_rewriteを使ったフロントコントローラー
 */
class SOY2ActionController implements SOY2_Controller{
	/**
	 * 準備
	 */
	public static function init($options = array()){
	}
	/**
	 * 実行
	 */
	public static function run(){
	}
	/**
	 * フロントコントローラー取得
	 */
	public static function getInstance(){
	}
	/**
	 * 他のURLへ移動
	 */
	public static function jump($url){
	}
	/**
	 * 現在のURLを再読込（queryは変更可能）
	 */
	public static function reload($query = null){
	}
	private $path;
	private $arguments = array();
}
/* SOY2/class/SOY2PageController.php */
/**
 * @package SOY2.controller
 */
class SOY2PageController implements SOY2_Controller{
	var $defaultPath = "Index";
	var $requestPath = "";
	var $arguments = array();
	public static function init($controller = null){
		static $_controller;
		if(!$_controller){
			if($controller){
				$_controller = new $controller();
			}else{
				$_controller = new SOY2PageController();
			}
		}
		return $_controller;
	}
	final public static function run(){
		$controller = self::init();
		$controller->execute();
	}
	final public static function getRequestPath(){
		$controller = self::init();
		return $controller->requestPath;
	}
	public static function getArguments(){
		$controller = self::init();
		return $controller->arguments;
	}
	function execute(){
		$pathBuilder = $this->getPathBuilder();
		$path = $pathBuilder->getPath();
		$args = $pathBuilder->getArguments();
		if(!strlen($path) || substr($path,strlen($path)-1,1) == "."){
			$path .= $this->getDefaultPath();
		}
		$this->requestPath = $path;
		$this->arguments = $args;
		$classPathBuilder = $this->getClassPathBuilder();
		$classPath = $classPathBuilder->getClassPath($path);
		$classPath .= 'Page';
		if(!SOY2HTMLFactory::pageExists($classPath)){
			$path = $pathBuilder->getPath();
			$classPath = $classPathBuilder->getClassPath($path);
			if(!preg_match('/.+Page$/',$classPath)){
				$classPath .= '.IndexPage';
			}
		}
		if(!SOY2HTMLFactory::pageExists($classPath)){
			$this->onNotFound($path, $args, $classPath);
		}
		$webPage = &SOY2HTMLFactory::createInstance($classPath, array(
			"arguments" => $args
		));
		try{
			$webPage->display();
		}catch(Exception $e){
			$this->onError($e);
		}
	}
	function onError(Exception $e){
		throw $e;
	}
	/**
	 * ページが存在しない場合
	 * 引数はオーバーロード用
	 * @param $path
	 * @param $args
	 * @param $classPath
	 */
	function onNotFound($path = null, $args = null, $classPath = null){
		header("HTTP/1.1 404 Not Found");
		header("Content-Type: text/html; charset=utf-8");
		echo "<h1>404 Not Found</h1><hr>指定のパスへのアクセスは有効でありません。";
		exit;
	}
	function getDefaultPath(){
		$controller = self::init();
		return $controller->defaultPath;
	}
	function setDefaultPath($path){
		$controller = self::init();
		$controller->defaultPath = $path;
	}
	public static function jump($path){
		$url = self::createLink($path, true);
		header("Location: ".$url);
		exit;
	}
	public static function redirect($path, $permanent = false){
		if($permanent){
			header("HTTP/1.1 301 Moved Permanently");
		}
		$url = self::createRelativeLink($path, true);
		header("Location: ".$url);
		exit;
	}
	public static function reload(){
		$url = self::createLink(self::getRequestPath(), true) ."/". implode("/",self::getArguments());
		header("Location: ".$url);
		exit;
	}
	function &getPathBuilder(){
		static $builder;
		if(!$builder){
			$builder = new SOY2_PathInfoPathBuilder();
		}
		return $builder;
	}
	function &getClassPathBuilder(){
		static $builder;
		if(!$builder){
			$builder = new SOY2_DefaultClassPathBuilder();
		}
		return $builder;
	}
	public static function createLink($path, $isAbsoluteUrl = false){
		$controller = self::init();
		$pathBuilder = $controller->getPathBuilder();
		return $pathBuilder->createLinkFromPath($path, $isAbsoluteUrl);
	}
	public static function createRelativeLink($path, $isAbsoluteUrl = false){
		$controller = self::init();
		$pathBuilder = $controller->getPathBuilder();
		return $pathBuilder->createLinkFromRelativePath($path, $isAbsoluteUrl);
	}
}
/**
 * @package SOY2.controller
 * PathInfoから呼び出しパスを作成
 * 後半の数字を含む部分は引数として渡す
 *
 * DOCUMENT_ROOTを仮想的にSOY2_DOCUMENT_ROOTで上書き可能
 */
class SOY2_PathInfoPathBuilder implements SOY2_PathBuilder{
	var $path;
	var $arguments;
	function __construct(){
		$pathInfo = (isset($_SERVER['PATH_INFO'])) ? $_SERVER['PATH_INFO'] : "";
		if(preg_match('/^((\/[a-zA-Z]*)*)(\/-)?((\/[0-9a-zA-Z_\.]*)*)$/',$pathInfo,$tmp)){
			$path = preg_replace('/^\/|\/$/',"",$tmp[1]);
			$path = str_replace("/",".",$path);
			$arguments = preg_replace("/^\//","",$tmp[4]);
			$arguments = explode("/",$arguments);
			foreach($arguments as $key => $value){
				if(!strlen($value)){
					$arguments[$key] = null;
					unset($arguments[$key]);
				}
			}
			$this->path = $path;
			$this->arguments = $arguments;
		}
	}
	function getPath(){
		return $this->path;
	}
	function getArguments(){
		return $this->arguments;
	}
	/**
	 * パスからURLを生成する
	 * スクリプトのファイル名を含む（ただし$pathが空の時はスクリプト名を付けない）
	 */
	function createLinkFromPath($path, $isAbsoluteUrl = false){
		$scriptPath = self::getScriptPath();
		if(strlen($path)>0){
			$path = $scriptPath . "/" . str_replace(".","/",$path);
		}else{
			$path = strrev(strstr(strrev($scriptPath),"/"));
		}
		if($isAbsoluteUrl){
			return self::createAbsoluteURL($path);
		}else{
			return $path;
		}
	}
	/**
	 * 相対パスを解釈してURLを生成する
	 * @param String $path 相対パス
	 * @param Boolean $isAbsoluteUrl 返り値を絶対URL（http://example.com/path/to）で返すかルートからの絶対パス（/path/to）で返すか
	 */
	function createLinkFromRelativePath($path, $isAbsoluteUrl = false){
		if(preg_match("/^https?:/",$path)){
			return $path;
		}
		if(preg_match("/^\//",$path)){
		}else{
			$scriptPath = self::getScriptPath();
			$scriptDir = preg_replace("/".basename($scriptPath)."\$/", "", $scriptPath);
			$path = self::convertRelativePathToAbsolutePath($path, $scriptDir);
		}
		if($isAbsoluteUrl){
			return self::createAbsoluteURL($path);
		}else{
			return $path;
		}
	}
	/**
	 * フロントコントローラーのURLでの絶対パスを取得する
	 * （ファイルシステムのパスではない）
	 */
	protected static function getScriptPath(){
		static $script;
		if(!$script){
			/**
			 * @TODO ルート的にアクセスされた場合は、フロントコントローラーの設置場所をDocumentRootとみなす。
			 */
			$documentRoot = (defined("SOY2_DOCUMENT_ROOT")) ? SOY2_DOCUMENT_ROOT : ((isset($_SERVER["SOY2_DOCUMENT_ROOT"])) ? $_SERVER["SOY2_DOCUMENT_ROOT"] : $_SERVER["DOCUMENT_ROOT"]);
			$documentRoot = str_replace("\\","/",$documentRoot);
			if(strlen($documentRoot) >0 && $documentRoot[strlen($documentRoot)-1] != "/") $documentRoot .= "/";
			$script = str_replace($documentRoot,"/",str_replace("\\","/",$_SERVER["SCRIPT_FILENAME"]));
			$script = str_replace("\\","/",$_SERVER["SCRIPT_FILENAME"]);
			$script = str_replace($documentRoot, "/", $script);
		}
		return $script;
	}
	/**
	 * 絶対パスにドメインなどを付加して絶対URLに変換する
	 */
	protected static function createAbsoluteURL($path){
		static $scheme, $domain, $port;
		if(!$scheme){
			$scheme = (isset($_SERVER["HTTPS"]) || defined("SOY2_HTTPS") && SOY2_HTTPS) ? "https" : "http";
		}
		if(!$domain && isset($_SERVER["SERVER_NAME"])){
			$domain = $_SERVER["SERVER_NAME"];
		}
		if(!$port){
			if(!isset($_SERVER["SERVER_PORT"])) $_SERVER["SERVER_PORT"] = 80;
			if( $_SERVER["SERVER_PORT"] == "80" && !isset($_SERVER["HTTPS"]) || $_SERVER["SERVER_PORT"] == "443" && isset($_SERVER["HTTPS"]) ){
				$port = "";
			}elseif(strlen($_SERVER["SERVER_PORT"]) > 0){
				$port = ":".$_SERVER["SERVER_PORT"];
			}else{
				$port = "";
			}
		}
		return $scheme."://".$domain.$port.$path;
	}
	/**
	 * 指定したディレクトリからの相対パスを絶対パスに変換する
	 */
	protected static function convertRelativePathToAbsolutePath($relativePath, $base){
		$base = str_replace("\\","/",$base);
		$base = preg_replace("/\/+/","/",$base);
		$relativePath = str_replace("\\","/",$relativePath);
		$relativePath = preg_replace("/\/+/","/",$relativePath);
		$dirs = explode("/", $base);
		if($dirs[0] == "") array_shift($dirs);
		if(count($dirs) > 0 && $dirs[count($dirs)-1] == "") array_pop($dirs);
		$paths = explode("/",$relativePath);
		$pathStack = array();
		foreach($paths as $path){
			if($path == ".."){
				array_pop($dirs);
			}elseif($path == "."){
			}else{
				array_push($pathStack,$path);
			}
		}
		$absolutePath = implode("/",array_merge($dirs,$pathStack));
		$absolutePath = "/".$absolutePath;
		return $absolutePath;
	}
}
/**
 * @package SOY2.controller
 */
class SOY2_DefaultClassPathBuilder implements SOY2_ClassPathBuilder{
	function getClassPath($path){
		return $path;
	}
}
/* SOY2Mail/SOY2Mail.php */
class SOY2Mail {
	/**
	 *
	 */
    public static function create($type, $options = array()){
		$mail = null;
    	switch($type){
    		case "imap":
    			$mail = new SOY2Mail_IMAPLogic($options);
    			break;
    		case "pop":
    			$mail = new SOY2Mail_POPLogic($options);
    			break;
    		case "smtp":
    			$mail = new SOY2Mail_SMTPLogic($options);
    			break;
    		case "sendmail":
    			$mail = new SOY2Mail_SendMailLogic($options);
    			break;
    		default:
    			throw new SOY2MailException("[SOY2Mail]Invalid Logic type " . $type);
    			break;
    	}
    	return $mail;
    }
    private $subject;
    private $encodedSubject;
    private $text;
    private $encodedText;
    private $attachments = array();
    private $headers = array();
    private $from = array();
    private $recipients = array();
    private $bccRecipients = array();
    private $encoding = "UTF-8";
    private $subjectEncoding = "ISO-2022-JP";
    private $rawData = "";
    function getSubject() {
    	return $this->subject;
    }
    function setSubject($subject) {
    	$this->subject = $subject;
    	$this->encodedSubject = "";
    }
    function getEncodedSubject() {
    	if(strlen($this->encodedSubject)<1){
    		$this->encodedSubject = mb_encode_mimeheader($this->subject,
				$this->getSubjectEncodingForConvert(),"B","\r\n",strlen("Subject: "));
    	}
    	return $this->encodedSubject;
    }
    function setEncodedSubject($encodedSubject) {
    	$this->encodedSubject = $encodedSubject;
    }
    function getText() {
    	return $this->text;
    }
    function setText($text, $encoding = null) {
    	$this->text = $text;
    	if(!$this->encodedText){
    		if(!$encoding)$encoding = $this->getEncodingForConvert();
    		$this->encodedText = mb_convert_encoding($text, $encoding);
    	}
    }
    function getEncodedText() {
    	return $this->encodedText;
    }
    function setEncodedText($encodedText) {
    	$this->encodedText = $encodedText;
    }
    function getAttachments() {
    	return $this->attachments;
    }
    function setAttachments($attachments) {
    	$this->attachments = $attachments;
    }
    function getHeaders() {
    	return $this->headers;
    }
    function setHeaders($headers) {
    	$this->headers = $headers;
    }
    function getFrom() {
    	return $this->from;
    }
    function setFrom($from, $label = null, $encoding = null) {
		if(!$encoding)$encoding = $this->getEncoding();
    	$this->from = new SOY2Mail_MailAddress($from, $label, $encoding);
    }
    function getRecipients() {
    	return $this->recipients;
    }
    function setRecipients($recipients) {
    	$this->recipients = $recipients;
    }
    function getEncodedRecipients() {
    	return $this->encodedRecipients;
    }
    function setEncodedRecipients($encodedRecipients) {
    	$this->encodedRecipients = $encodedRecipients;
    }
    /**
     * 本文の文字コード
     * 件名の文字コードは別のプロパティ：subjectEncoding
     */
	function getEncoding() {
		return $this->encoding;
	}
	function setEncoding($encoding) {
		$this->encoding = $encoding;
	}
	function getBccRecipients() {
    	return $this->bccRecipients;
    }
    function setBccRecipients($bccRecipients) {
    	$this->bccRecipients = $bccRecipients;
    }
    function getRawData(){
    	return $this->rawData;
    }
    function setRawData($rawData){
    	$this->rawData = $rawData;
    }
    /**
     * 件名をリセットする
     */
    function clearSubject(){
    	$this->subject = null;
    	$this->encodedSubject = null;
    }
    /**
     * 本文をリセットする
     */
    function clearText(){
    	$this->text = null;
    	$this->encodedText = null;
    }
    /**
     * 受信者を追加する
     */
    function addRecipient($address, $label = null, $encoding = null){
    	if(!$encoding)$encoding = $this->getEncoding();
    	$recipient = new SOY2Mail_MailAddress($address, $label, $encoding);
    	$this->recipients[$address] = $recipient;
    	return $this;
    }
    /**
     * 受信者を削除する
     */
    function removeRecipient($address){
    	$this->recipients[$address] = null;
    	unset($this->recipients[$address]);
    }
    /**
     * 受信者をすべて削除する
     */
    function clearRecipients(){
    	$this->recipients = array();
    }
    /**
     * BCC受信者を追加する
     */
    function addBccRecipient($address, $label = null, $encoding = null){
    	if(!$encoding)$encoding = $this->getEncoding();
    	$recipient = new SOY2Mail_MailAddress($address, $label, $encoding);
    	$this->bccRecipients[$address] = $recipient;
    	return $this;
    }
    /**
     * BCC受信者を削除する
     */
    function removeBccRecipient($address){
    	$this->bccRecipients[$address] = null;
    	unset($this->bccRecipients[$address]);
    }
    /**
     * BCC受信者をすべて削除する
     */
    function clearBccRecipients(){
    	$this->bccRecipients = array();
    }
    /**
     * headerを追加する
     */
    function setHeader($key, $value){
    	if(strlen($value)>0){
	    	$this->headers[$key] = $value;
    	}else{
    		if(array_key_exists($key, $this->headers)){
    			unset($this->headers[$key]);
    		}
    	}
    	return $this;
    }
    /**
     * ヘッダーを設定する
     */
    function getHeader($key){
    	return (isset($this->headers[$key])) ? $this->headers[$key] : "";
    }
    /**
     * ヘッダーをリセットする
     */
    function clearHeaders(){
    	$this->headers = array();
    }
    /**
     * 添付ファイルを追加する
     */
    function addAttachment($filename, $type, $contents){
    	$this->attachments[$filename] = array(
    		"filename" => $filename,
    		"mime-type" => $type,
    		"contents" => $contents
    	);
    }
    /**
     * 添付ファイルを削除する
     */
    function removeAttachment($filename){
    	$this->attachments[$filename] = null;
    	unset($this->attachments[$filename]);
    }
    /**
     * 添付ファイルをすべて削除する
     */
    function clearAttachments(){
    	$this->attachments = array();
    }
	/**
	 * 件名の文字コード
	 */
    function getSubjectEncoding() {
    	return $this->subjectEncoding;
    }
    function setSubjectEncoding($subjectEncoding) {
    	$this->subjectEncoding = $subjectEncoding;
    }
    /**
     * 文字コード変換のための本文の文字コード
     */
    function getEncodingForConvert(){
    	return self::getPracticalEncoding($this->getEncoding());
    }
    /**
     * 文字コード変換のための件名の文字コード
     */
    function getSubjectEncodingForConvert(){
    	return self::getPracticalEncoding($this->getSubjectEncoding());
    }
    /**
     * 文字コード変換に使用する文字コードを返す
     * ヘッダーなどに記載する文字コードとは別の文字コードを変換に使用するために用意した
     */
    public static function getPracticalEncoding($encoding){
    	switch(strtoupper($encoding)){
    		case "ISO-2022-JP":
    			/*
    			 * 半角カナが文字化けしないように
    			 * ISO-2022-JP: ASCII, JIS X 0201 のラテン文字, JIS X 0208
    			 * JIS: ISO-2022-JPに加え、JIS X 0201の半角カナ, JIS X 0212
    			 * ISO-2022-JP-MS: JISに加え、NEC特殊文字とNEC選定IBM拡張文字を扱える
    			 *   http://legacy-encoding.sourceforge.jp/wiki/
    			 */
    			if(version_compare(PHP_VERSION,"5.2.1") >= 0){
	    			return "ISO-2022-JP-MS";
    			}else{
	    			return "JIS";
    			}
    		default:
    			return $encoding;
    	}
    }
}
class SOY2Mail_MailAddress{
	private $address;
	private $label;
	private $encoding;
	function __construct($address, $label = "", $encoding = ""){
		$this->address = $address;
		$this->label = $label;
		$this->encoding = $encoding;
	}
	function getAddress() {
		if(strpos($this->address, '"') === false && ( strpos($this->address, "..") !== false || strpos($this->address, ".@") !== false )){
			list($local, $domain) = explode("@", $this->address);
			$quoted = '"'.$local.'"@'.$domain;
			return $quoted;
		}else{
			return $this->address;
		}
	}
	function setAddress($address) {
		$this->address = $address;
	}
	function getLabel() {
		return $this->label;
	}
	function setLabel($label) {
		$this->label = $label;
	}
	function getEncoding() {
		return $this->encoding;
	}
	function setEncoding($encoding) {
		$this->encoding = $encoding;
	}
    /**
     * 文字コード変換のための文字コード
     */
    function getEncodingForConvert(){
    	return SOY2Mail::getPracticalEncoding($this->getEncoding());
    }
    /**
     * メールヘッダー記載用の文字列
     * ダブルクオートは使わない
     */
	function getString(){
		if(strlen($this->address)<1)return '';
		if(strlen($this->label)<1)return '<' . $this->address . '>';
		return mb_encode_mimeheader($this->label, $this->getEncodingForConvert()).' <'.$this->address.'>';
	}
	function __toString(){
		return $this->getString();
	}
	/**
	 * メールアドレスの書式チェック
	 * @param string $email
	 * @param boolean trueなら厳密なチェックを行なわない
	 * @return boolean
	 *
	 * $lazy: true
	 * @の前後に1文字以上、ドメイン部に.区切りの文字列があればOK
	 * $lazy: false
	 * 使える文字がRFC準拠。
	 * ただしローカルパート部のドット「.」の連続や末尾のドットがあってもNGとはしない（docomoなどのRFC違反アドレスを許容する）。
	 */
	protected static function _validation($email, $lazy = false){
		if($lazy){
			$validEmail = "^.+\@[^.]+(?:\\.[^.]+)+\$";
		}else{
			$ascii  = '[a-zA-Z0-9!#$%&\'*+\-\/=?^_`{|}~.]';//'[\x01-\x7F]';
			$domain = '(?:[-a-z0-9]+\.)+[a-z]{2,10}';//'([-a-z0-9]+\.)*[a-z]+';
			$d3     = '\d{1,3}';
			$ip     = $d3.'\.'.$d3.'\.'.$d3.'\.'.$d3;
			$validEmail = "^$ascii+\@(?:$domain|\\[$ip\\])\$";
		}
		if(! preg_match('/'.$validEmail.'/i', $email) ) {
			return false;
		}
		return true;
	}
	/**
	 * メールアドレスの書式チェック（簡易）
	 * @param string $email
	 * @return boolean
	 */
	public static function simpleValidation($email){
		return self::_validation($email, true);
	}
	/**
	 * メールアドレスの書式チェック（やや厳密）
	 * @param string $email
	 * @return boolean
	 */
	public static function validation($email){
		return self::_validation($email, false);
	}
}
interface SOY2Mail_SenderInterface{
	function open();
	function send();
	function close();
}
interface SOY2Mail_ReceiverInterface{
	function open();
	function receive();
	function close();
}
class SOY2MailException extends Exception{}
/* SOY2Mail/SOY2Mail_IMAPLogic.class.php */
class SOY2Mail_IMAPLogic extends SOY2Mail implements SOY2Mail_ReceiverInterface{
	private $con;
	private $host;
	private $port;
	private $flag;
	private $folder;
	private $user;
	private $pass;
	function __construct($options) {
		if(!function_exists("imap_open")){//extension_loaded("imap")
			throw new SOY2MailException("The extension 'imap' is necessary.");
		}
		if(!isset($options["imap.host"])){
			throw new SOY2MailException("[imap.host] is necessary.");
		}
		if(!isset($options["imap.port"])){
			throw new SOY2MailException("[imap.port] is necessary.");
		}
		if(!isset($options["imap.user"])){
			throw new SOY2MailException("[imap.user] is necessary.");
		}
		if(!isset($options["imap.pass"])){
			throw new SOY2MailException("[imap.pass] is necessary.");
		}
		$this->host = $options["imap.host"];
		$this->port = $options["imap.port"];
		if(isset($options["imap.flag"]))$this->flag = $options["imap.flag"];
		if(isset($options["imap.folder"]))$this->folder = $options["imap.folder"];
		$this->user = $options["imap.user"];
		$this->pass = $options["imap.pass"];
	}
	function __destruct(){
		if($this->con) $this->close();
	}
	function open(){
		$host = $this->host;
		$host .= ":" . $this->port;
		if($this->flag)$host .= "/" . $this->flag;
		$this->con = imap_open("{" . $host . "}" . $this->folder, $this->user, $this->pass);
		if($this->con === false){
			throw new SOY2MailException("imap_open(): login failed");
		}
	}
	function close(){
		imap_close($this->con);
		$this->con = null;
	}
	function receive(){
		if(!$this->con)$this->open();
		$unseen = imap_search($this->con, "UNSEEN");
		if($unseen == false){
			return false;
		}
		$mail = new SOY2Mail();
		$i = array_shift($unseen);
		$head = imap_headerinfo($this->con, $i);
		$title = mb_decode_mimeheader(@$head->subject);
		$rawHeader = imap_fetchheader($this->con, $i);
		$mail->setRawData($rawHeader.imap_body($this->con, $i));
		$Structure = imap_fetchstructure($this->con, $i);	//メール構造読み込み
		$mimeType = $this->getMimeType($Structure->type,$Structure->subtype);	//メールタイプ読み込み
		if(strpos($mimeType,"multipart") !== false && count($Structure->parts)>1){
			$numberOfParts = count($Structure->parts);	//添付ファイルの数数え
			for($j=1; $j<$numberOfParts; $j++){
				$part = $Structure->parts[$j];
				if($part->ifdparameters){
					$filename = $this->getParameterValue($part->dparameters,"filename");
				}
				if(!$filename && $part->ifparameters){
					$filename = $this->getParameterValue($part->parameters,"name");
				}
				if($filename){
					$attachmentName = $filename;	//添付ファイル名読み込み
					$attachmentName = mb_encode_mimeheader($attachmentName);//日本語名だったらエンコード
				}else{
					$attachmentName = "file-".$i."-".$j;
				}
				$attachmentFile = imap_fetchbody ($this->con,$i,$j+1);	//添付部分取り出し
				$attachmentFile = imap_base64 ($attachmentFile);		//デコード
				$mail->addAttachment($attachmentName, $this->getMimeType($part->type,$part->subtype), $attachmentFile);
			}
			$body = imap_fetchbody($this->con, $i, 1);
			if($encoding = $this->getParameterValue($Structure->parts[0]->parameters,"charset")){
				$mail->setEncoding($encoding);
			}else{
				$encoding = null;
			}
		}else{
			if($encoding = $this->getParameterValue($Structure->parameters,"charset")){
				$mail->setEncoding($encoding);
			}else{
				$encoding = null;
			}
			$body = imap_body($this->con, $i);
		}
		imap_setflag_full($this->con, $i, "\\Seen");
		$from = $head->from[0];
		$mail->setFrom($from->mailbox . "@" . $from->host, @$from->personal);
		$to = $head->to[0];
		$mail->addRecipient($to->mailbox . "@" . $to->host, @$to->personal);
		$mail->setSubject($title);
		$mail->setEncodedText($body);
		if($encoding){
			$mail->setText(mb_convert_encoding($body,"UTF-8",$encoding));
		}else{
			$mail->setText(mb_convert_encoding($body,"UTF-8","JIS,SJIS,EUC-JP,UTF-8,ASCII"));
		}
		$mail->setHeaders((array)$head);
		return $mail;
	}
	/**
	 * imap_fetchstructureの返り値のオブジェクトのtypeとsubtypeからMIME-Typeをテキストで返す
	 */
	function getMimeType($type, $subType){
		$mimeType = "";
		switch($type){
			case 0:
				$mimeType = "text";
				break;
			case 1:
				$mimeType = "multipart";
				break;
			case 2:
				$mimeType = "message";
				break;
			case 3:
				$mimeType = "application";
				break;
			case 4:
				$mimeType = "audio";
				break;
			case 5:
				$mimeType = "image";
				break;
			case 6:
				$mimeType = "video";
				break;
			case 7:
				$mimeType = "other";
				break;
		}
		if(strlen($subType)){
			$mimeType .= "/".strtolower($subType);
		}
		return $mimeType;
	}
	/**
	 * imap_fetchstructureの返り値のオブジェクトのparametersから欲しいattributeの値を返す
	 */
	function getParameterValue($parameters, $attribute){
		$attribute = strtolower($attribute);
		foreach($parameters as $param){
			if(strtolower($param->attribute) == $attribute){
				return $param->value;
			}
		}
		return false;
	}
	function getCon() {
		return $this->con;
	}
	function setCon($con) {
		$this->con = $con;
	}
	function getHost() {
		return $this->host;
	}
	function setHost($host) {
		$this->host = $host;
	}
	function getPort() {
		return $this->port;
	}
	function setPort($port) {
		$this->port = $port;
	}
	function getFlag() {
		return $this->flag;
	}
	function setFlag($flag) {
		$this->flag = $flag;
	}
	function getUser() {
		return $this->user;
	}
	function setUser($user) {
		$this->user = $user;
	}
	function getPass() {
		return $this->pass;
	}
	function setPass($pass) {
		$this->pass = $pass;
	}
}
/* SOY2Mail/SOY2Mail_POPLogic.class.php */
class SOY2Mail_POPLogic extends SOY2Mail implements SOY2Mail_ReceiverInterface{
	private $con;
	private $host;
	private $port;
	private $flag;
	private $folder;
	private $user;
	private $pass;
	function __construct($options){
		if(!isset($options["pop.host"])){
			throw new SOY2MailException("[pop.host] is necessary.");
		}
		if(!isset($options["pop.port"])){
			throw new SOY2MailException("[pop.port] is necessary.");
		}
		if(!isset($options["pop.user"])){
			throw new SOY2MailException("[pop.user] is necessary.");
		}
		if(!isset($options["pop.pass"])){
			throw new SOY2MailException("[pop.pass] is necessary.");
		}
		$this->host = $options["pop.host"];
		$this->port = $options["pop.port"];
		if(isset($options["pop.flag"]))$this->flag = $options["pop.flag"];
		if(isset($options["pop.folder"]))$this->folder = $options["pop.folder"];
		$this->user = $options["pop.user"];
		$this->pass = $options["pop.pass"];
	}
	function __destruct(){
		if($this->con) $this->close();
	}
	function open(){
		$this->con = fsockopen($this->host, $this->port, $errono, $errnstr);
		if(!$this->con){
			$this->close();
			throw new SOY2MailException("failed to connect");
		}
		$buff = $this->popCommand("USER ".$this->user);
		if(!$buff)throw new SOY2MailException("Failed to connect pop server");
		$buff = $this->popCommand("PASS ".$this->pass);
		if(!$buff)throw new SOY2MailException("Failed to connect pop server");
	}
	function close(){
		if($this->con){
			$this->popCommand("QUIT");
			fclose($this->con);
			$this->con = null;
		}
	}
	function receive(){
		if(!$this->con)$this->open();
		$res = $this->popCommand("LIST");
		if(!$res)throw new SOY2MailException("failed to open Receive Server");
		$mailId = null;
		while(true){
			$buff = $this->getPopResponse();
			if($buff == ".")break;
			$array = explode(" ",$buff);
			if(!is_numeric($array[0]))continue;
			if(!$mailId)$mailId = $array[0];
		}
		if(!$mailId)return false;
		$res = $this->popCommand("RETR ".$mailId);
		$flag = false;
		$header = "";
		$body = "";
		$encoding = "JIS";
		$headers = array();
		$mail = new SOY2Mail();
		while(true){
			$buff = $this->getPopResponse();
			if($buff == ".")break;
			if(!$flag && strlen($buff)==0){
				$flag = true;
				continue;
			}
			if(strpos($buff,"..")===0){
				$buff = substr($buff,1);
			}
			if($flag){
				$body .= $buff . "\r\n";
			}else{
				$header .= $buff . "\r\n";
			}
		}
		$this->popCommand("DELE " . $mailId);
		$mail->setRawData($header."\r\n".$body);
		$headers = $this->parseHeaders($header);
		if(isset($headers["Content-Type"]) && preg_match("/boundary=\"?(.*?)\"?/",$headers["Content-Type"], $tmp)){
			$boundary = $tmp[1];
			$bodies = explode("--". $boundary, $body);
			$attachCount = count($bodies);
			for($i=0;$i<$attachCount;++$i){
				$tmpHeader = substr($bodies[$i], 0, strpos($bodies[$i], "\r\n\r\n"));
				$tmpBody = substr($bodies[$i], strpos($bodies[$i], "\r\n\r\n")+4);
				$tmpHeaders = $this->parseHeaders($tmpHeader);
				if(isset($tmpHeaders["Content-Disposition"]) && preg_match("/filename.*=(.*)/",$tmpHeaders["Content-Disposition"], $tmp)){
					$filename = preg_replace('/["\']/',"",$tmp[1]);
					$mail->addAttachment($filename, "", base64_decode($tmpBody));
					continue;
				}
				if(isset($tmpHeaders["Content-Type"]) && preg_match("/charset=(.*)/",$tmpHeaders["Content-Type"],$tmp)){
					$encoding = $tmp[1];
					$body = $tmpBody;
				}
			}
		}else{
			if(isset($headers["Content-Type"]) && preg_match("/charset=(.*)/",$headers["Content-Type"],$tmp)){
				$encoding = $tmp[1];
			}
		}
		if(isset($headers["From"])){
			$from = explode(",",$headers["From"]);
			$from = trim($from[0]);
			if(preg_match('/"?(.*?)"?\s*<?(.+@.+)>?/',$from,$tmp)){
				$label = mb_decode_mimeheader($tmp[1]);
				$address = $tmp[2];
				$mail->setFrom($address, $label);
			}
		}
		if(isset($headers["To"])){
			$toes = explode(",",$headers["To"]);
			foreach($toes as $to){
				$to = trim($to);
				if(preg_match('/"?(.*?)"?\s?<?(.+@.+)>?/',$to,$tmp)){
					$label = mb_decode_mimeheader($tmp[1]);
					$address = $tmp[2];
					$mail->addRecipient($address, $label);
				}
			}
		}
		if(isset($headers["Subject"])){
			$mail->setSubject(mb_decode_mimeheader(@$headers["Subject"]));
		}
		$mail->setHeaders($headers);
		$mail->setEncodedText($body);
		$mail->setText(mb_convert_encoding($body,"UTF-8",$encoding));
		$mail->setEncoding($encoding);
		return $mail;
	}
	function popCommand($string){
		fputs($this->con, $string."\r\n");
  		$buff = fgets($this->con);
		if(strpos($buff,"+OK") == 0){
			return $buff;
		}else{
			return false;
		}
	}
	function getPopResponse(){
		$buff = fgets($this->con);
		$buff = rtrim($buff, "\r\n");
		return $buff;
	}
	/**
	 * 受信メッセージのヘッダーを解析し配列にする
	 */
	function parseHeaders($header){
		$headers = array();
		$header = preg_replace("/\r\n[ \t]+/", ' ', $header);
		$raw_headers = explode("\r\n", $header);
		foreach($raw_headers as $value){
			$name  = substr($value, 0, $pos = strpos($value, ':'));
			$value = ltrim(substr($value, $pos + 1));
			if (isset($headers[$name]) AND is_array($headers[$name])) {
				$headers[$name][] = $value;
			} elseif (isset($headers[$name])) {
				$headers[$name] = array($headers[$name], $value);
			} else {
				$headers[$name] = $value;
			}
		}
		return $headers;
	}
}
/* SOY2Mail/SOY2Mail_SMTPLogic.class.php */
class SOY2Mail_SMTPLogic extends SOY2Mail implements SOY2Mail_SenderInterface{
	private $con;
	private $host;
	private $port;
	private $isSMTPAuth = false;
	private $isStartTLS = false;
	private $user;
	private $pass;
	private $debugHTML = false;
	private $debug = false;
	private $esmtpOptions = array();
	private $isSecure = false;
	function __construct($options){
		if(!isset($options["smtp.host"])){
			throw new SOY2MailException("[smtp.host] is necessary.");
		}
		if(!isset($options["smtp.port"])){
			throw new SOY2MailException("[smtp.port] is necessary.");
		}
		$this->host = $options["smtp.host"];
		$this->port = $options["smtp.port"];
		$this->isSMTPAuth = (isset($options["smtp.auth"])) ? $options["smtp.auth"] : false;
		$this->isStartTLS = (isset($options["smtp.starttls"])) ? $options["smtp.starttls"] : false;
		$this->user =  (isset($options["smtp.user"])) ? $options["smtp.user"] : null;
		$this->pass =  (isset($options["smtp.pass"])) ? $options["smtp.pass"] : null;
		if(isset($options["debug"]) && $options["debug"]){
			if(isset($_SERVER["REMOTE_ADDR"])){
				$this->debugHTML = true;
			}else{
				$this->debug = true;
			}
		}
	}
	function open(){
		$this->con = fsockopen($this->host, $this->port, $errono, $errnstr, 60);
		if(!$this->con){
			$this->close();
			throw new SOY2MailException("failed to connect");
		}
		stream_set_timeout($this->con, 1);
		$buff = $this->getSmtpResponse();
		if(substr($buff,0,3) != "220"){
			throw new SOY2MailException("failed to receive greeting message.");
		}
		$this->ehlo();
		if(stripos($this->host, 'ssl://') === 0 || stripos($this->host, 'tls://') === 0){
			$this->isSecure = true;
		}elseif(
			$this->isStartTLS &&//使う設定
			function_exists("openssl_open") &&//OpenSSLが利用可能 extension_loaded("openssl")
			function_exists("stream_socket_enable_crypto") &&//PHP 5.1.0以上
			isset($this->esmtpOptions['STARTTLS'])//STARTTLSが利用可能
		){
			if( $this->startTLS() ){
				$this->isSecure = true;
				$this->ehlo();
			}
		}
		if($this->isSMTPAuth && isset($this->esmtpOptions["AUTH"]) && is_array($this->esmtpOptions["AUTH"])){
			$authTypes = $this->esmtpOptions["AUTH"];
			/** CRAM-MD5を最優先にしてDIGEST-MD5の優先度を下げる **/
			if(in_array("CRAM-MD5",$authTypes)){
				$this->smtpCommand("AUTH CRAM-MD5");
				$buff = $this->getSmtpResponse();
				if(strlen($buff) < 5 || substr($buff,0,3) != "334") throw new SOY2MailException("smtp login failed");
				$challenge = base64_decode(substr(($buff),4));
				$response = SOY2Mail_SMTPAuth_CramMD5::getResponse($this->user,$this->pass,$challenge);
				$this->smtpCommand(base64_encode($response));
				while(true){
					$buff = $this->getSmtpResponse();
					if(substr($buff,0,3) == "235") break;
					if(substr($buff,0,3) == "501") throw new SOY2MailException("smtp login failed: wrong parameter");
					if(substr($buff,0,3) == "535") throw new SOY2MailException("smtp login failed: wrong id or password");
				}
			}else if(in_array("DIGEST-MD5",$authTypes)){
				$hostname = str_replace("ssl://", "", $this->host);
				$this->smtpCommand("AUTH DIGEST-MD5");
				$buff = $this->getSmtpResponse();
				if(strlen($buff) < 5 || substr($buff,0,3) != "334") throw new SOY2MailException("smtp login failed");
				$challenge = base64_decode(substr(trim($buff),4));
				$response = SOY2Mail_SMTPAuth_DigestMD5::getResponse($this->user,$this->pass,$challenge,$hostname);
				$this->smtpCommand(base64_encode($response));
				while(true){
					$buff = $this->getSmtpResponse();
					if(substr($buff,0,3) == "334") break;
					if(substr($buff,0,3) == "501") throw new SOY2MailException("smtp login failed: wrong parameter");
					if(substr($buff,0,3) == "535") throw new SOY2MailException("smtp login failed: wrong id or password");
				}
				$this->smtpCommand("");
				while(true){
					$buff = $this->getSmtpResponse();
					if(substr($buff,0,3) == "235") break;
					if(substr($buff,0,3) == "501") throw new SOY2MailException("smtp login failed: wrong parameter");
					if(substr($buff,0,3) == "535") throw new SOY2MailException("smtp login failed: wrong id or password");
				}
			}elseif(in_array("PLAIN",$authTypes)){
				$this->smtpCommand("AUTH PLAIN ".base64_encode(
					$this->user . "\0" .
					$this->user . "\0" .
					$this->pass ));
				while(true){
					$buff = $this->getSmtpResponse();
					if(substr($buff,0,3) == "235") break;
					if(substr($buff,0,3) == "501") throw new SOY2MailException("smtp login failed: wrong parameter");
					if(substr($buff,0,3) == "535") throw new SOY2MailException("smtp login failed: wrong id or password");
				}
			}elseif(in_array("LOGIN",$authTypes)){
				$this->smtpCommand("AUTH LOGIN");
				$buff = $this->getSmtpResponse();
				if(substr($buff,0,3) != "334") throw new SOY2MailException("smtp login failed");
				$this->smtpCommand(base64_encode($this->user));
				$buff = $this->getSmtpResponse();
				if(substr($buff,0,3) != "334") throw new SOY2MailException("smtp login failed");
				$this->smtpCommand(base64_encode($this->pass));
				while(true){
					$buff = $this->getSmtpResponse();
					if(substr($buff,0,3) == "235") break;
					if(substr($buff,0,3) == "501") throw new SOY2MailException("smtp login failed: wrong parameter");
					if(substr($buff,0,3) == "535") throw new SOY2MailException("smtp login failed: wrong id or password");
				}
			}else{
			}
		}
	}
	function ehlo(){
		$this->smtpCommand("EHLO ". php_uname("n"));// gethostname php_uname("n") $_SERVER["HOSTNAME"]
		$buff = $this->getSmtpResponse();//最初はドメイン
		while(strlen($buff) && substr($buff,0,4) != "250 "){
			$buff = $this->getSmtpResponse();
			if(preg_match("/^250[- ]([-A-Z0-9]+)(?:[= ](.*))?\$/i",trim($buff),$matches)){
				if(isset($matches[2])){
					if(strpos($matches[2]," ")!==false){
						$this->esmtpOptions[$matches[1]] = explode(" ",$matches[2]);
					}else{
						$this->esmtpOptions[$matches[1]] = $matches[2];
					}
				}else{
					$this->esmtpOptions[$matches[1]] = true;
				}
			}
		}
	}
	function startTLS(){
		$this->smtpCommand("STARTTLS");
		$buff = $this->getSmtpResponse();
		if( substr($buff,0,3) == "220" ){
			return stream_socket_enable_crypto($this->con, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
		}
	}
	function send(){
		$bccRecipients = $this->getBccRecipients();
		$recipients = $this->getRecipients();
		foreach($recipients as $recipient){
			$this->sendMail($recipient, $bccRecipients);
		}
	}
	function sendMail(SOY2Mail_MailAddress $sendTo,$bccRecipients = array()){
		$sent = false;
		$try = $try_connect = 0;
		while(!$sent){
			$try++;
			while(!$this->con){
				$try_connect++;
				try{
					$this->open();
				}catch(Exception $e){
					$this->close();
					if($try_connect > 10){
						if($this->debug)echo "SMTP Failed to open SMTP connection.\n";
						throw $e;
					}
				}
				if($try_connect > 20){
					$this->close();
					throw new SOY2MailException("Too many failure to connect server.");
				}
			}
			try{
				$this->_sendMail($sendTo, $bccRecipients);
				$sent = true;
			}catch(Exception $e){
				$this->close();
				if($try > 2){
					throw $e;
				}
			}
			if($try > 5){
				$this->close();
				throw new SOY2MailException("Too many failure to send email.");
			}
		}
	}
	private function _sendMail(SOY2Mail_MailAddress $sendTo,$bccRecipients = array()){
		$from = $this->getFrom();
		$title = $this->getEncodedSubject();
		$body = $this->getEncodedText();
		$body = str_replace(array("\r\n", "\r"), "\n", $body);  // CRLF, CR -> LF 正規表現で m オプションを使うためにLFにする
		$body = preg_replace('/^\\./m','..', $body);          // .～        -> ..～
		$body = str_replace("\n", "\r\n", $body);                // LF       -> CRLF
		$this->smtpCommand("MAIL FROM:<".$from->getAddress().">");
		while(true){
			$str = $this->getSmtpResponse();
			if(substr($str,0,3) == "250") break;
			if(!is_null($str) && strlen($str)<1)sleep(1);
			if(strlen($str) && substr($str,0,3)!="250")throw new SOY2MailException("Failed: MAIL FROM " . $str);
		}
		$this->isSendMailFrom = true;
		$this->smtpCommand("RCPT TO:<".$sendTo->getAddress().">");
		foreach($bccRecipients as $bccSendTo){
			$this->smtpCommand("RCPT TO:<".$bccSendTo->getAddress().">");
		}
		while(true){
			$str = $this->getSmtpResponse();
			if(strlen($str)<1)break;
			if(preg_match("/Ok/i",$str)) break;
			if(substr($str,0,3)!="250")throw new SOY2MailException("Failed: RCPT TO " . $str);
		}
		$this->smtpCommand("DATA");
		while(true){
			$str = $this->getSmtpResponse();
			if(strlen($str)<1)break;
			if(preg_match("/354/i",$str)) break;
			if(substr($str,0,3)!="250")throw new SOY2MailException("Failed: DATA " . $str);
		}
		$headers = $this->getHeaders();
		foreach($headers as $key => $value){
			if( "Content-Type" == $key ){ continue; }
			$this->data("$key: $value");
		}
		$this->data("MIME-Version: 1.0");
		$this->data("Subject: ".$title);
		$this->data("From: ".$from->getString());
		$this->data("To: ".$sendTo->getString());
		$attachments = $this->getAttachments();
		if(count($attachments)<1){
			if(isset($headers["Content-Type"])){
				$this->data("Content-Type: ".$headers["Content-Type"]);
			}else{
				$this->data("Content-Type: text/plain; charset=".$this->getEncoding()."");
			}
			$this->data("");
			$this->data($body);
		}else{
			$boundary = "----------" . md5(time());
			$this->data("Content-Type: multipart/mixed;  boundary=\"$boundary\"");
			$this->data("");
			$this->data("--".$boundary);
			if(isset($headers["Content-Type"])){
				$this->data("Content-Type: ".$headers["Content-Type"]);
			}else{
				$this->data("Content-Type: text/plain; charset=".$this->getEncoding()."");
			}
			$this->data("");
			$this->data($body);
			foreach($attachments as $filename => $attachment){
				if( !isset($attachment["contents"]) ){ continue; }
				$this->data("--".$boundary);
				if( !isset($attachment["mime-type"]) || strlen($attachment["mime-type"]) <1 ){
					$attachment["mime-type"] = "application/octet-stream";
				}
				$this->data("Content-Type: ".$attachment["mime-type"].";"."\r\n"." name=\"".mb_encode_mimeheader($filename)."\"");
				$this->data("Content-Disposition: inline;"."\r\n"." filename=\"".mb_encode_mimeheader($filename)."\"");
				$this->data("Content-Transfer-Encoding: base64");
				$this->data("");
				$this->data(wordwrap(base64_encode($attachment["contents"]),72, "\r\n", true));
			}
			$this->data("--". $boundary . "--");
		}
		$this->smtpCommand(".");
	}
	function close(){
		if($this->con && $this->smtpCommand("QUIT")){
			fclose($this->con);
		}
		$this->con = null;
	}
	function data($string){
		$this->smtpCommand($string);
	}
	function smtpCommand($string){
		if(!$this->con){
			throw new SOY2MailException('SMTP is null');
 			return;
 		}
 		if($this->debugHTML)echo "> ". htmlspecialchars($string) . "<br>";
		if($this->debug)echo "SMTP> ".$string."\n";
 		$result = fputs($this->con, $string."\r\n");
 		if($result == false){
			throw new SOY2MailException('Result is false.');
 		}
	}
	function getSmtpResponse(){
		$buff = fgets($this->con);
		while($buff === false || !strlen($buff)){
			$buff = fgets($this->con);
			$meta = stream_get_meta_data($this->con);
			if(feof($this->con) || $meta["timed_out"]){
				return null;
			}
		}
		if($this->debugHTML)echo "> ". htmlspecialchars($buff) . "<br>";
		if($this->debug)echo "SMTP< ".$buff;
		return $buff;
	}
	function getCon() {
		return $this->con;
	}
	function setCon($con) {
		$this->con = $con;
	}
	function getHost() {
		return $this->host;
	}
	function setHost($host) {
		$this->host = $host;
	}
	function getPort() {
		return $this->port;
	}
	function setPort($port) {
		$this->port = $port;
	}
	function getIsSMTPAuth() {
		return $this->isSMTPAuth;
	}
	function setIsSMTPAuth($isSMTPAuth) {
		$this->isSMTPAuth = $isSMTPAuth;
	}
	function getUser() {
		return $this->user;
	}
	function setUser($user) {
		$this->user = $user;
	}
	function getPass() {
		return $this->pass;
	}
	function setPass($pass) {
		$this->pass = $pass;
	}
	function getDebug() {
		return $this->debug;
	}
	function setDebug($debug) {
		$this->debug = $debug;
	}
}
class SOY2Mail_SMTPAuth_CramMD5{
	static function getResponse($user, $pass, $challengeStr){
		return $user." ".hash_hmac("md5",$challengeStr,$pass);
	}
}
class SOY2Mail_SMTPAuth_DigestMD5{
	static function getResponse($user, $pass, $challengeStr, $hostname){
		$challenge = array();
		if(preg_match_all('/([-a-z]+)=(?:"([^"]*)"|([^=,]*))(?:,|$)/u',$challengeStr,$matches,PREG_SET_ORDER)){
			foreach($matches as $matche){
				$challenge[$matche[1]] = strlen($matche[2]) ? $matche[2] : $matche[3] ;
			}
		}
		if(!isset($challenge["algorithm"]) || !isset($challenge["nonce"])){
			throw new SOY2MailException("smtp login failed");
		}
		if(isset($challenge["qop"]) && strlen($challenge["qop"])){
			$qop = explode(",",$challenge["qop"]);
			if(!in_array("auth",$qop)){
				throw new SOY2MailException("smtp login failed");
			}
		}
		if(!isset($challenge["realm"])){
			$challenge["realm"] = "";
		}
		if(!isset($challenge["maxbuf"])){
			$challenge["maxbuf"] = "65536";
		}
		$response = array(
			"username" => $user,
			"realm" => $challenge["realm"],
			"nonce" => $challenge["nonce"],
			"cnonce" => preg_replace("/[^[:alnum:]]/", "", substr(base64_encode( md5(mt_rand(),true) ),0,21)),
			"nc" => "00000001",
			"qop" => "auth",
			"digest-uri" => 'smtp/'.$hostname,
			"response" => "",
			"maxbuf" => $challenge["maxbuf"],
		);
		$hashed = pack('H32', md5($user.":".$response["realm"].":".$pass));
		$a1 = $hashed.":".$response["nonce"].":".$response["cnonce"];
		$a2 = "AUTHENTICATE:".$response["digest-uri"];
		$response["response"] = md5(md5($a1).":".$response["nonce"].":".$response["nc"].":".$response["cnonce"].":".$response["qop"].":".md5($a2));
		$responseArr = array();
		foreach($response as $key => $value){
			$isContinue = false;
			switch($key){
				case "realm":
					if(!strlen($value)) $isContinue = false;
					break;
				case "username":
				case "nonce":
				case "cnonce":
				case "digest-uri":
					$value = '"'.$value.'"';
					break;
			}
			if(!$isContinue) continue;
			$responseArr[] = $key."=".$value;
		}
		$resonseStr = implode(",",$responseArr);
		return $resonseStr;
	}
}
/* SOY2Mail/SOY2Mail_SendMailLogic.class.php */
class SOY2Mail_SendMailLogic extends SOY2Mail implements SOY2Mail_SenderInterface{
    function __construct($options) {
    }
    function open(){}
    function close(){}
    function send(){
    	$bccRecipients = $this->getBccRecipients();
    	$recipients = $this->getRecipients();
		foreach($recipients as $recipient){
			$this->sendMail($recipient, $bccRecipients);
		}
    }
    function sendMail($sendTo,$bccRecipients = array()){
		$to = $sendTo->getString();
		$from = $this->getFrom();
		$title = $this->getEncodedSubject();
		$body = $this->getEncodedText();
		$headers = array();
		$_headers = $this->getHeaders();
		foreach($_headers as $key => $value){
			if( "Content-Type" == $key ){ continue; }
			$headers[] = "$key: $value";
		}
		$headers[] = "MIME-Version: 1.0" ;
		$headers[] = "From: " . $from->getString();
		$attachments = $this->getAttachments();
		if(count($attachments)<1){
			if(isset($_headers["Content-Type"])){
				$headers[] = "Content-Type: ".$_headers["Content-Type"];
			}else{
				$headers[] = "Content-Type: text/plain; charset=".$this->getEncoding();
			}
		}else{
			$boundary = "----------" . md5(time());
			$headers[] = "Content-Type: multipart/mixed;  boundary=\"$boundary\"";
			$_body = "--" . $boundary . "\r\n";
			if(isset($_headers["Content-Type"])){
				$_body .= "Content-Type: ".$_headers["Content-Type"] . "\r\n";
			}else{
				$_body .= "Content-Type: text/plain; charset=".$this->getEncoding()."" . "\r\n";
			}
			$body = $_body . "\r\n" . $body . "\r\n";
			foreach($attachments as $filename => $attachment){
				if( !isset($attachment["contents"]) ){ continue; }
				$body .= "--" . $boundary . "\r\n";
				if( !isset($attachment["mime-type"]) || strlen($attachment["mime-type"]) <1 ){
					$attachment["mime-type"] = "application/octet-stream";
				}
				$body .= "Content-Type: ".$attachment["mime-type"].";"."\r\n".
				         " name=\"".mb_encode_mimeheader($filename)."\"" . "\r\n";
				$body .= "Content-Disposition: inline;"."\r\n".
				         " filename=\"".mb_encode_mimeheader($filename)."\"" . "\r\n";
				$body .= "Content-Transfer-Encoding: base64" . "\r\n";
				$body .= "\r\n";
				$body .= wordwrap(base64_encode($attachment["contents"]),72, "\r\n", true) . "\r\n";
			}
			$body .= "--" . $boundary . "--";
		}
		/**
		 * RFC2821 4.5.2：SMTPクライアントは .から始まる行に.を付加し、サーバーは .から始まる行の.を除去する
		 * ただし、sendmailに渡す場合は「.」の処理はsendmailがやってくれる。
		 * Windows版mail()ではPHPがSMTP通信を行うが「.」の処理はPHP側がやってくれる。
		 */
		/**
		 * 改行コードはLF（Windows版mail()はSMTP通信を行うのでCRLF）を使う
		 *
		 * PHPマニュアルにはヘッダーの改行コードはCRLFとあるがこれは間違い。
		 * mail()の改行コードの扱いは問題がある。
		 * Manual: http://jp2.php.net/manual/ja/function.mail.php
		 * Bug report: http://bugs.php.net/15841
		 * http://www.webmasterworld.com/forum88/4368.htm
		 *
		 * RFC2822ではメールの改行コードはCRLFだが、
		 * *nix版PHPのmailはSMTP通信を行うのではなくsendmailコマンドを使うため
		 * ローカル環境の改行コードを使って値を渡すのが正しいとも言える。
		 * そのためmail()内部ではadditional_headerとTo:, Subject, 本文をLFで結合している。
		 * メール末尾もLF.LFとなっている。
		 *
		 * 改行コードをLFに統一してもmail()にはまだ問題がある。
		 * CRLF＋スペースorタブ以外の制御コードはいったんスペースに置換しているようで、
		 * ヘッダーのfoldingのためのLF+スペースがスペース＋スペースに置換されたまま戻らなくなってしまう。
		 * http://www.pubbs.net/php/200908/44353/
		 * RFC2822では一行は998文字までがMUST、78文字はSHOULDなのでRFC違反ではない。
		 *
		 * ただし、改行コードがLFのメールをsendmailに渡してもCRLFに変換してくれないことが
		 * 多いようなので、改行コードについてはRFC違反となる。
		 * が、CRLFとLFが混在するよりはましなので、LFで統一することにする。
		 */
		$title = str_replace(array("\r\n", "\r"), "\n", $title);
		$body = str_replace(array("\r\n", "\r"), "\n", $body);
		$to = str_replace(array("\r\n", "\r"), "\n", $to);
		$headersText = implode("\n",$headers);
		if($this->isWindows()){
			$title = str_replace("\n", "\r\n", $title);
			$body = str_replace("\n", "\r\n", $body);
			$to = str_replace("\n", "\r\n", $to);
			$headersText = implode("\r\n",$headers);
		}
		$sendmail_params  = "-f".$from->getAddress();
		mail($to, $title, $body, $headersText, $sendmail_params);
		if(count($bccRecipients) >0){
			$headers[] = "X-To: ".$sendTo->getString();
			if($this->isWindows()){
				$headersText = implode("\r\n",$headers);
			}else{
				$headersText = implode("\n",$headers);
			}
			foreach($bccRecipients as $bccSendTo){
				$to = $bccSendTo->getString();
				$to = str_replace(array("\r\n", "\r"), "\n", $to);
				if(isset($_SERVER["WINDIR"]) || isset($_SERVER["windir"])){
					$to = str_replace("\n", "\r\n", $to);
				}
				mail($to, $title, $body, $headersText, $sendmail_params);
			}
		}
    }
    /**
     * OSがWindowsかどうかを返す
     */
    private function isWindows(){
		if(isset($_SERVER["WINDIR"]) || isset($_SERVER["windir"])){
			return true;
		}elseif(isset($_SERVER["SystemRoot"]) && strpos(strtolower($_SERVER["SystemRoot"]),"windows") !== false){
			return true;
		}elseif(isset($_SERVER["SYSTEMROOT"]) && strpos(strtolower($_SERVER["SYSTEMROOT"]),"windows") !== false){
			return true;
		}else{
			return false;
		}
    }
}
/* SOY2Mail/SOY2Mail_ServerConfig.class.php */
/**
 * SOY2Mail 標準サーバ設定クラス
 *
 * SOY2Mail#importを使うにはSOY2が必要です。
 */
class SOY2Mail_ServerConfig {
    const SERVER_TYPE_SMTP = 0;
	const SERVER_TYPE_SENDMAIL = 2;
	const RECEIVE_SERVER_TYPE_POP  = 0;
	const RECEIVE_SERVER_TYPE_IMAP = 1;
    private $sendServerType = SOY2Mail_ServerConfig::SERVER_TYPE_SENDMAIL;
    private $isUseSMTPAuth = true;
    private $isUsePopBeforeSMTP = false;
    private $sendServerAddress = "localhost";
    private $sendServerPort = 25;
    private $sendServerUser = "";
    private $sendServerPassword = "";
    private $isUseSSLSendServer = false;
    private $receiveServerType = SOY2Mail_ServerConfig::RECEIVE_SERVER_TYPE_POP;
    private $receiveServerAddress = "localhost";
    private $receiveServerPort = 110;
    private $receiveServerUser = "";
    private $receiveServerPassword = "";
    private $isUseSSLReceiveServer = false;
    private $fromMailAddress = "";
    private $fromMailAddressName = "";
    private $returnMailAddress = "";
    private $returnMailAddressName = "";
    private $encoding = "ISO-2022-JP";
    /**
     * 設定からSOY2Mailオブジェクトを生成する
     */
    function buildReceiveMail(){
    	switch($this->receiveServerType){
    		case self::RECEIVE_SERVER_TYPE_IMAP:
    			$flag = null;
    			if($this->getIsUseSSLReceiveServer())$flag = "ssl";
    			return SOY2Mail::create("imap",array(
    				"imap.host" => $this->getReceiveServerAddress(),
    				"imap.port" => $this->getReceiveServerPort(),
    				"imap.user" => $this->getReceiveServerUser(),
    				"imap.pass" => $this->getReceiveServerPassword(),
    				"imap.flag" => $flag
    			));
    			break;
    		case self::RECEIVE_SERVER_TYPE_POP:
    		default:
    			$host = $this->getReceiveServerAddress();
    			if($this->getIsUseSSLReceiveServer())$host =  "ssl://" . $host;
    			return SOY2Mail::create("pop",array(
    				"pop.host" => $host,
    				"pop.port" => $this->getReceiveServerPort(),
    				"pop.user" => $this->getReceiveServerUser(),
    				"pop.pass" => $this->getReceiveServerPassword()
    			));
    			break;
    	}
    }
    /**
     * 設定からSOY2Mailオブジェクトを生成する
     */
    function buildSendMail(){
    	$mail = null;
    	switch($this->sendServerType){
    		case self::SERVER_TYPE_SMTP:
    			$host = $this->getSendServerAddress();
    			if($this->getIsUseSSLSendServer())$host =  "ssl://" . $host;
    			$mail = SOY2Mail::create("smtp",array(
    				"smtp.host" => $host,
    				"smtp.port" => $this->getSendServerPort(),
    				"smtp.user" => $this->getSendServerUser(),
    				"smtp.pass" => $this->getSendServerPassword(),
    				"smtp.auth" => ($this->getIsUseSMTPAuth()) ? "PLAIN" : false
    			));
    			break;
    		case self::SERVER_TYPE_SENDMAIL:
    		default:
    			$mail = SOY2Mail::create("sendmail",array());
    			break;
    	}
    	if($mail){
    		$mail->setEncoding($this->getEncoding());
    		$mail->setSubjectEncoding($this->getEncoding());
    		$mail->setFrom($this->getFromMailAddress(),$this->getFromMailAddressName());
			if(strlen($this->getReturnMailAddress())>0){
				$replyTo = new SOY2Mail_MailAddress($this->getReturnMailAddress(), $this->getReturnMailAddressName(), $this->getEncoding());
				$mail->setHeader("Reply-To", $replyTo->getString());
			}
    	}
    	return $mail;
    }
    /**
     * export config
     */
    function export(){
    	return base64_encode(addslashes(serialize($this)));
    }
    /**
     * import config
     */
    function import($str){
    	$obj = unserialize(stripslashes($str));
    	if($obj && $obj instanceof SOY2Mail_ServerConfig){
    		SOY2::cast($this,$obj);
    	}else{
    		throw new SOY2MailException("Failed to import");
    	}
    }
    function getSendServerType() {
    	return $this->sendServerType;
    }
    function setSendServerType($sendServerType) {
    	$this->sendServerType = $sendServerType;
    }
    function getIsUseSMTPAuth() {
    	return $this->isUseSMTPAuth;
    }
    function setIsUseSMTPAuth($isUseSMTPAuth) {
    	$this->isUseSMTPAuth = $isUseSMTPAuth;
    }
    function getIsUsePopBeforeSMTP() {
    	return $this->isUsePopBeforeSMTP;
    }
    function setIsUsePopBeforeSMTP($isUsePopBeforeSMTP) {
    	$this->isUsePopBeforeSMTP = $isUsePopBeforeSMTP;
    }
    function getSendServerAddress() {
    	return $this->sendServerAddress;
    }
    function setSendServerAddress($sendServerAddress) {
    	$this->sendServerAddress = $sendServerAddress;
    }
    function getSendServerPort() {
    	return $this->sendServerPort;
    }
    function setSendServerPort($sendServerPort) {
    	$this->sendServerPort = $sendServerPort;
    }
    function getSendServerUser() {
    	return $this->sendServerUser;
    }
    function setSendServerUser($sendServerUser) {
    	$this->sendServerUser = $sendServerUser;
    }
    function getSendServerPassword() {
    	return $this->sendServerPassword;
    }
    function setSendServerPassword($sendServerPassword) {
    	$this->sendServerPassword = $sendServerPassword;
    }
    function getIsUseSSLSendServer() {
    	return $this->isUseSSLSendServer;
    }
    function setIsUseSSLSendServer($isUseSSLSendServer) {
    	$this->isUseSSLSendServer = $isUseSSLSendServer;
    }
    function getReceiveServerType() {
    	return $this->receiveServerType;
    }
    function setReceiveServerType($receiveServerType) {
    	$this->receiveServerType = $receiveServerType;
    }
    function getReceiveServerAddress() {
    	return $this->receiveServerAddress;
    }
    function setReceiveServerAddress($receiveServerAddress) {
    	$this->receiveServerAddress = $receiveServerAddress;
    }
    function getReceiveServerPort() {
    	return $this->receiveServerPort;
    }
    function setReceiveServerPort($receiveServerPort) {
    	$this->receiveServerPort = $receiveServerPort;
    }
    function getReceiveServerUser() {
    	return $this->receiveServerUser;
    }
    function setReceiveServerUser($receiveServerUser) {
    	$this->receiveServerUser = $receiveServerUser;
    }
    function getReceiveServerPassword() {
    	return $this->receiveServerPassword;
    }
    function setReceiveServerPassword($receiveServerPassword) {
    	$this->receiveServerPassword = $receiveServerPassword;
    }
    function getIsUseSSLReceiveServer() {
    	return $this->isUseSSLReceiveServer;
    }
    function setIsUseSSLReceiveServer($isUseSSLReceiveServer) {
    	$this->isUseSSLReceiveServer = $isUseSSLReceiveServer;
    }
    function getFromMailAddress() {
    	return $this->fromMailAddress;
    }
    function setFromMailAddress($fromMailAddress) {
    	$this->fromMailAddress = $fromMailAddress;
    }
    function getFromMailAddressName() {
    	return $this->fromMailAddressName;
    }
    function setFromMailAddressName($fromMailAddressName) {
    	$this->fromMailAddressName = $fromMailAddressName;
    }
    function getReturnMailAddress() {
    	return $this->returnMailAddress;
    }
    function setReturnMailAddress($returnMailAddress) {
    	$this->returnMailAddress = $returnMailAddress;
    }
    function getReturnMailAddressName() {
    	return $this->returnMailAddressName;
    }
    function setReturnMailAddressName($returnMailAddressName) {
    	$this->returnMailAddressName = $returnMailAddressName;
    }
    function getEncoding() {
    	return $this->encoding;
    }
    function setEncoding($encoding) {
    	$this->encoding = $encoding;
    }
}
/* SOY2Action/SOY2Action.php */
/**
 * @package SOY2.SOY2Action
 */
class SOY2Action extends SOY2ActionBase{
	const SUCCESS = "_success_";
	const FAILED = "_failed_";
	/**
	 * Action実行前準備
	 */
	protected function prepareImpl(SOY2ActionRequest	&$request,
							   SOY2ActionResponse	$response){
		return (method_exists($this,"prepare")) ? $this->prepare($request,$response) : null;
	}
	/**
	 * Action実行
	 */
	protected function executeImpl(SOY2ActionRequest	&$request,
							   SOY2ActionForm		&$form,
							   SOY2ActionResponse	&$response){
		return (method_exists($this,"execute")) ? $this->execute($request,$form,$response) : null;
	}
	/**
	 * Action実行。
	 * Get時に実行される。
	 */
	protected function doGetImpl(SOY2ActionRequest	&$request,
							   SOY2ActionForm		&$form,
							   SOY2ActionResponse	&$response){
		return (method_exists($this,"doGet")) ? $this->doGet($request,$form,$response) : null;
	}
	/**
	 * Action実行
	 * Post時に実行される。
	 */
	protected function doPostImpl(SOY2ActionRequest	&$request,
							   SOY2ActionForm		&$form,
							   SOY2ActionResponse	&$response){
		return (method_exists($this,"doPost")) ? $this->doPost($request,$form,$response) : null;
	}
	/**
	 * Action実行後処理
	 */
	protected function clearanceImpl(SOY2ActionResponse	&$response){
		if(method_exists($this,"clearance"))$this->clearance($response);
		$response = null;
	}
	/**
	 * Action実行処理
	 * @final
	 */
	final function run(){
		$request =& SOY2ActionRequest::getInstance();
		$response =& SOY2ActionResponse::getInstance();
		$this->_result = new SOY2ActionResult();
		$this->prepareImpl($request,$response);
		$formName = $this->getActionFormName();
		$form = SOY2ActionForm::createForm($formName,$request);
		if($request->getMethod() == 'POST'){
			$result = $this->doPostImpl($request,$form,$response);
			if($result)$this->_result->setResult($result);
		}else if($request->getMethod() == 'GET'){
			$result = $this->doGetImpl($request,$form,$response);
			if($result)$this->_result->setResult($result);
		}
		$result = $this->executeImpl($request,$form,$response);
		if($result)$this->_result->setResult($result);
		$this->clearanceImpl($response);
		return $this->_result;
	}
	/**
	 * メッセージをリザルトオブジェクトに設定
	 *
	 * @param キー
	 * @param 値
	 */
	function setMessage($key,$value){
		$this->_result->setMessage($key,$value);
	}
	/**
	 * リザルトオブジェクトからメッセージを取得
	 *
	 * @param　キー（省略可。省略時は全て）
	 */
	function getMessage($key = null){
		return $this->_result->getMessage($key);
	}
	/**
	 * エラーメッセージをリザルトオブジェクトに設定
	 *
	 * @param キー
	 * @param 値
	 */
	function setErrorMessage($key,$value){
		$this->_result->setErrorMessage($key,$value);
	}
	/**
	 * リザルトオブジェクトからエラーメッセージを取得
	 *
	 * @param キー (省略可。省略時は全て)
	 */
	function getErrorMessage($key = null){
		return $this->_result->getErrorMessage($key);
	}
	/**
	 * リザルトオブジェクトに属性を設定
	 *
	 * @param キー
	 * @param 値
	 */
	function setAttribute($key,$obj){
		$this->_result->setAttribute($key,$obj);
	}
	/**
	 * リザルトオブジェクトから属性を取得
	 *
	 * @param キー
	 */
	function getAttribute($key){
		return $this->_result->getAttribute($key);
	}
	/**
	 * ActionFormのクラス名を取得
	 * ディフォルトはクラス名+Form
	 *
	 * オーバーライドすることでActionFormのクラスを変更することが可能
	 */
	function getActionFormName(){
		return get_class($this). "Form";
	}
	/**
	 * ユーザーセッションを呼び出す
	 *
	 * @return SOY2UserSession
	 */
	function getUserSession(){
		return SOY2ActionSession::getUserSession();
	}
	/**
	 * Flashセッションを呼び出す
	 *
	 * @return SOY2FlashSession
	 */
	function getFlashSession(){
		return SOY2ActionSession::getFlashSession();
	}
}
/**
 * @package SOY2.SOY2Action
 */
class SOY2ActionResult{
	private $_result;
	private $_message;
	private $_errorMessage;
	private $_attributes;
	function setResult($result){
		switch($result){
			case SOY2Action::SUCCESS:
			case SOY2Action::FAILED:
				$this->_result = $result;
				break;
			default:
				throw new Exception("SOY2Action must return SOY2Action::SUCCESS or SOY2Action::FAILED.");
		}
	}
	function __toString(){
		return $this->_result;
	}
	function setMessage($key,$value){
		$this->_message[$key] = $value;
	}
	function getMessage($key = null){
		if(is_null($key)){
			return $this->_message;
		}
		return (isset($this->_message[$key])) ? $this->_message[$key] : null;
	}
	function setErrorMessage($key,$value){
		$this->_errorMessage[$key] = $value;
	}
	function getErrorMessage($key = null){
		if(is_null($key)){
			return $this->_errorMessage;
		}
		return (isset($this->_errorMessage[$key])) ? $this->_errorMessage[$key] : null;
	}
	function setAttribute($key,$obj){
		$this->attributes[$key] = $obj;
	}
	function getAttribute($key){
		if(is_null($key)){
			return $this->attributes;
		}
		return (isset($this->attributes[$key])) ? $this->attributes[$key] : null;
	}
	/**
	 * booleanを返します。
	 *
	 * PHP 5.2.0 以前では__toStringが使えないのでこちらを使用してください。
	 *
	 * @return boolean 成功か、失敗か
	 */
	function success(){
		return ($this->_result == SOY2Action::SUCCESS) ? true : false;
	}
}
/**
 * @package SOY2.SOY2Action
 */
class SOY2ActionConfig{
	private function __construct(){}
	private $actionPath = "actions/";
	private static function &getInstance(){
		static $_static;
		if(!$_static){
			$_static = new SOY2ActionConfig();
		}
		return $_static;
	}
	public static function ActionDir($dir = null){
		$config = self::getInstance();
		if($dir){
			if(substr($dir,strlen($dir)-1) != '/'){
				throw new Exception("[SOY2Action]ActionDir must end by '/'.");
			}
			$config->actionPath = str_replace("\\", "/", $dir);
		}
		return $config->actionPath;
	}
}
/**
 * @package SOY2.SOY2Action
 * SOY2ActionFactory
 * SOY2Actionオブジェクトを作成します。
 */
class SOY2ActionFactory extends SOY2ActionBase{
	static function &createInstance($path,$attributes = array()){
		$obj = null;
		if(class_exists($path)){
			$obj = new $path();
		}else{
			$tmp = array();
			if(preg_match('/\.?([a-zA-Z0-9]+$)/',$path,$tmp)){
				$className = $tmp[1];
			}
			if(!class_exists($className)){
				$fullPath = SOY2ActionConfig::ActionDir(). str_replace(".","/",$path).".class.php";
				if(defined("SOY2ACTION_AUTO_GENERATE") && SOY2ACTION_AUTO_GENERATE == true && !file_exists($fullPath)){
					SOY2ActionFactory::generateAction($className,$fullPath,$attributes);
				}
				include($fullPath);
			}
			$obj = new $className();
		}
		foreach($attributes as $key => $value){
			if(method_exists($obj,"set".ucwords($key))){
				$func = "set".ucwords($key);
				$obj->$func($value);
				continue;
			}
		}
		return $obj;
	}
	private static function generateAction($className,$fullPath,$attributes){
		$dirpath = dirname($fullPath);
		while(realpath($dirpath) == false){
			if(!mkdir($dirpath))return;
			$dirpath = dirname($dirpath);
		}
		$docComment = array();
		$docComment[] = "/**";
		$docComment[] = " * @class $className";
		$docComment[] = " * @date ".date("c");
		$docComment[] = " * @author SOY2ActionFactory";
		$docComment[] = " */ ";
		$class = array();
		$class[] = "class ".$className." extends SOY2Action{";
		if(!empty($attributes)){
			$class[] = "	";
			$setter = array();
			foreach($attributes as $key => $value){
			$class[] = '	private $'.$key.';';
			$setter[] = '	';
			$setter[] = '	function set'.ucwords($key).'($'.$key.'){';
			$setter[] = '		$this->'.$key.' = $'.$key.';';
			$setter[] = '	}';
			$setter[] = '	';
			}
			$class[] = implode("\n",$setter);
		}
		$class[] = "	";
		$class[] = "	/**";
		$class[] = "	 * Actionの実行を行います。";
		$class[] = "	 */";
		$class[] = '	protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form, SOY2ActionResponse &$response){';
		$class[] = "		";
		$class[] = "		//フォームにエラーが発生していた場合";
		$class[] = '		if($form->hasError()){';
		$class[] = '			foreach($form->getErrors() as $key => $value){';
		$class[] = '				$this->setErrorMessage($key,$form->getErrorString($key));';
		$class[] = '			}';
		$class[] = '			return SOY2Action::FAILED;';
		$class[] = '		}';
		$class[] = "		";
		$class[] = "		";
		$class[] = "		return SOY2Action::SUCCESS;";
		$class[] = "	}";
		$class[] = "}";
		if(!empty($_POST)){
			$class[] = "";
			$class[] = "class ".$className."Form extends SOY2ActionForm{";
			$setter = array();
			foreach($_POST as $key => $value){
			$class[] = '	var $'.$key.';';
			$setter[] = '	';
			$setter[] = '	/**';
			$setter[] = '	 * @validator string {}';
			$setter[] = '	 */';
			$setter[] = '	function set'.ucwords($key).'($'.$key.'){';
			$setter[] = '		$this->'.$key.' = $'.$key.';';
			$setter[] = '	}';
			$setter[] = '	';
			}
			$class[] = implode("\n",$setter);
			$class[] = "}";
		}
		file_put_contents($fullPath,"<?php \n".implode("\n",$docComment) ."\n". implode("\n",$class)."\n?>");
	}
}
/* SOY2Action/soy2action/SOY2ActionBase.class.php */
/**
 * @package SOY2.SOY2Action
 */
class SOY2ActionBase{
	private $_classPath;
	protected function getClassPath(){
		if(is_null($this->_soy2_classPath)){
			$reflection = new ReflectionClass(get_class($this));
			$classFilePath = $reflection->getFileName();
			$this->_soy2_classPath = str_replace("\\", "/", $classFilePath);
		}
		return $this->_classPath;
	}
}
/* SOY2Action/soy2action/SOY2ActionForm.class.php */
/**
 * @package SOY2.SOY2Action
 */
class SOY2ActionForm{
	var $_errors = array();
	function getParamName(){}
	/**
	 * プロパティ名を指定してエラー表示
	 * @return boolean
	 */
	function isError($propName){
		return (isset($this->_errors[$propName]) && is_a($this->_errors[$propName],'ActionFormError')) ? true : false;
	}
	/**
	 * バリデートのエラーが存在するかどうか
	 * @return boolean
	 */
	function hasError(){
		return (count($this->_errors) > 0 ) ? true : false;
	}
	/**
	 * プロパティ名を指定してエラーメッセージを取得
	 * @return string エラーメッセージ
	 */
	function getErrorString($propName){
		$error = @$this->_errors[$propName];
		return ($error) ? $error->format() : null;
	}
	/**
	 * プロパティ名を指定してエラーオブジェクトを取得
	 * @return ActionFormError
	 */
	function getError($propName){
		$error = @$this->_errors[$propName];
		return $error;
	}
	/**
	 * エラーを設定
	 */
	function setError($propName,ActionFormError $error){
		$this->_errors[$propName] = $error;
	}
	/**
	 * エラーを全て取得
	 */
	function getErrors(){
		return $this->_errors;
	}
	/**
	 * @access public static
	 * フォームを作成
	 *
	 * @param フォーム名
	 * @param SOY2ActionRequest
	 */
	public static function createForm($formName,&$request){
		if(!class_exists($formName)){
			return new SOY2ActionForm();
		}
		$form = new $formName();
		$reflection = new ReflectionClass($formName);
		if($form->getParamName()){
			$param = $request->getParameter($form->getParamName());
		}else{
			$param = $request->getParameters();
		}
		if(!is_array($param)){
			return $form;
		}
		foreach($param as $key => $value){
			$param[strtolower($key)] = $value;
		}
		$reflectionProperties = $reflection->getProperties();
		foreach($reflectionProperties as $property){
			$funcName = "set".ucwords($property->getName());
			try{
				$method = $reflection->getMethod($funcName);
				if($method->isInternal()){
					continue;
				}
			}catch(Exception $e){
				continue;
			}
			$value = @$param[strtolower($property->getName())];
			$validator = SOY2ActionFormValidator::getValidator($param,$property,$method);
			if($validator){
				$value = $validator->validate($form,$property->getName(),$value,$validator->_isRequire);
			}
			$form->$funcName($value);
		}
		return $form;
	}
	function __toString(){
		$values = array();
		foreach($this as $key => $value){
			if($key == "_errors")continue;
			$values[$key] = $value;
		}
		return (string)http_build_query($values);
	}
}
/**
 * フォームバリデートのエラー保持クラス
 */
class ActionFormError{
	var $className;
	var $prop;
	var $validator;
	var $error;
	var $message;
	/**
	 * @param string $class ActionFormクラス名
	 * @param string $prop プロパティ名
	 * @param string $validator Validator名
	 * @param string $error エラー種別
	 */
	function __construct($class,$prop,$validator,$error,$message = null){
		$this->className = $class;
		$this->prop = $prop;
		$this->validator = $validator;
		$this->error = $error;
		$this->message = $message;
	}
	function getFormat(){
		return '$class->$propは$validatorの$error に違反しています';
	}
	function format(){
		if($this->message){
			return $this->message;
		}
		$format = $this->getFormat();
		$format = str_replace('$class',$this->className,$format);
		$format = str_replace('$prop',$this->prop,$format);
		$format = str_replace('$validator',$this->validator,$format);
		$format = str_replace('$error',$this->error,$format);
		return $format;
	}
	function __toString(){
		return $this->format();
	}
}
/* SOY2Action/soy2action/SOY2ActionFormValidator.class.php */
/**
 * @package SOY2.SOY2Action
 *
 * ヴァリデート
 *
 * 使い方
 * setterのコメントに
 * @validator ＜形式＞ ＜オプション＞
 *
 * オプションは省略化
 * ヴァリデーターによって設定出来るオプションはさまざま
 *
 */
abstract class SOY2ActionFormValidator{
	var $_isRequire;
	var $_message;
	/**
	 * @access public
	 * 対応するバリデータを取得。
	 * 無かったらnullを返す。
	 *
	 * @return SOY2ActionFormValidator
	 */
	public static function getValidator($param,ReflectionProperty &$property,ReflectionMethod &$reflectionMethod){
		$comment = $reflectionMethod->getDocComment();
		$comment = preg_replace('/^\s*\*|^\/\*\*|\/$|\n/m','',$comment);
		$tmp = array();
		if(!preg_match('/@validator\s+([^\s]*)(?:\s+(\{.*\}))?/m',$comment,$tmp))return null;
		$type = $tmp[1];
		$json = @$tmp[2];
		$class = "SOY2ActionFormValidator_".ucwords($type)."Validator";
		if(!class_exists($class)){
			throw new Exception("[SOY2ActionFormValidator]".$class." is not defined.");
		}
		$obj = json_decode($json);
		$validator = new $class($obj,$param);
		$validator->_isRequire = (isset($obj->require)) ? $obj->require : false;
		if(!empty($obj->message)){
			$validator->_message = (array)$obj->message;
		}
		return $validator;
	}
	function getMessage($error){
		return (isset($this->_message[$error])) ? $this->_message[$error] : null;
	}
	abstract function validate(SOY2ActionForm &$form,$propName,$value,$isRequire);
}
/* SOY2Action/soy2action/SOY2ActionFormValidators.php */
/**
 * @package SOY2.SOY2Action
 */
class SOY2ActionFormValidator_NumberValidator extends SOY2ActionFormValidator{
	var $max;
	var $min;
	function __construct($obj){
		$this->max = @$obj->max;
		$this->min = @$obj->min;
	}
	function validate(SOY2ActionForm &$form,$propName,$value,$require){
		if($require && is_null($value)){
			$form->setError($propName,new ActionFormError(get_class($form),$propName,get_class($this),"require",$this->getMessage("require")));
		}
		if(!$require && is_null($value)){
			return null;
		}
		if(!is_numeric($value)){
			$form->setError($propName,new ActionFormError(get_class($form),$propName,get_class($this),"type",$this->getMessage("type")));
		}
		if(isset($this->max) && (int)$this->max < (int)$value){
			$form->setError($propName,new ActionFormError(get_class($form),$propName,get_class($this),"max",$this->getMessage("max")));
		}
		if(isset($this->min) && (int)$this->min > (int)$value){
			$form->setError($propName,new ActionFormError(get_class($form),$propName,get_class($this),"min",$this->getMessage("min")));
		}
		return $value;
	}
}
/**
 * @package SOY2.SOY2Action
 */
class SOY2ActionFormValidator_StringValidator extends SOY2ActionFormValidator{
	var $max;
	var $min;
	var $regex;
	function __construct($obj){
		$this->max = @$obj->max;
		$this->min = @$obj->min;
		$this->regex = @$obj->regex;
	}
	function validate(SOY2ActionForm &$form,$propName,$value,$require){
		if($require && strlen($value) < 1){
			$form->setError($propName,new ActionFormError(get_class($form),$propName,get_class($this),"require",$this->getMessage("require")));
		}
		if(!$require && strlen($value) < 1){
			return null;
		}
		if(isset($this->max) && $this->max < strlen($value)){
			$form->setError($propName,new ActionFormError(get_class($form),$propName,get_class($this),"max",$this->getMessage("max")));
		}
		if(isset($this->min) && $this->min > strlen($value)){
			$form->setError($propName,new ActionFormError(get_class($form),$propName,get_class($this),"min",$this->getMessage("min")));
		}
		if(isset($this->regex) && !preg_match("/".$this->regex."/",$value)){
			$form->setError($propName,new ActionFormError(get_class($form),$propName,get_class($this),"regex",$this->getMessage("regex")));
		}
		return $value;
	}
}
/* SOY2Action/soy2action/SOY2ActionRequest.class.php */
/**
 * @package SOY2.SOY2Action
 */
class SOY2ActionRequest{
	private $_hash;
	private $_method;
	public static function &getInstance(){
		static  $_static;
		if(!$_static){
			$_static = new SOY2ActionRequest();
			$_static->_hash = array_merge($_POST,$_GET);
			$_static->_method = $_SERVER['REQUEST_METHOD'];
		}
		return $_static;
	}
	function getCookies(){
	}
	function getHeader($name){
	}
	function getMethod(){
		return $this->_method;
	}
	function setMethod($method){
		$this->_method = $method;
	}
	function getParameter($name){
		return (isset($this->_hash[$name])) ? $this->_hash[$name] : null;
	}
	function getParameterNames(){
		return array_keys($this->_hash);
	}
	function &getParameters(){
		return $this->_hash;
	}
	function setParameter($key,$value){
		$this->_hash[$key] = $value;
	}
	function addParameter($key,$value){
		if(!isset($this->_hash[$key])){
			$this->_hash[$key] = array($value);
			return;
		}
		if(is_array($this->_hash[$key])){
			$this->_hash[$key][] = $value;
		}else{
			$this->_hash[$key] = array($this->_hash[$key],$value);
		}
	}
}
/* SOY2Action/soy2action/SOY2ActionResponse.class.php */
/**
 * @package SOY2.SOY2Action
 */
class SOY2ActionResponse{
	private $_header = array();
	public static function &getInstance(){
		static  $_static;
		if(!$_static){
			$_static = new SOY2ActionResponse();
		}
		return $_static;
	}
	/**
	 * @todo デストラクタでヘッダー送信はスマートじゃないかも。でも壊されない限り呼ばれないしな
	 */
	function __destruct(){
		foreach($this->_header as $key => $value){
			header($key.": ".$value);
		}
	}
	function addHeader($key,$value){
		if(!is_array(@$this->_header[$key])){
			$this->_header[$key] = array(@$this->header[$key]);
		}
		$this->_header[$key] = $value;
	}
	function sendRedirect($url){
		header("Location: ".$url);
	}
	function setHeader($key,$value){
		$this->_header[$key] = $value;
	}
}
/* SOY2Action/soy2action/SOY2ActionSession.class.php */
/**
 * @package SOY2.SOY2Action
 */
class SOY2ActionSession {
    const session_user_key = "_SOY2_USER_";
    const session_flash_key = "_SOY2_FLASH_";
    /**
     * @return SOY2UserSession
     */
    public static function &getUserSession(){
		if(session_status() == PHP_SESSION_NONE) session_start();
    	if(!isset($_SESSION[self::session_user_key])){
    		$_SESSION[self::session_user_key] = new SOY2UserSession();
    	}
    	return $_SESSION[self::session_user_key];
    }
    /**
     * @return SOY2FlashSession
     */
    public static function &getFlashSession(){
		if(session_status() == PHP_SESSION_NONE) session_start();
    	static $_request;
    	if(is_null($_request)){
    		$_request = true;
    	}
    	if(!isset($_SESSION[self::session_flash_key])){
    		$_SESSION[self::session_flash_key] = new SOY2FlashSession();
    	}
    	if($_request == true){
    		$_SESSION[self::session_flash_key]->checkFlash();
    		$_request = false;
    	}
    	return $_SESSION[self::session_flash_key];
    }
    public static function regenerateSessionId(){
		if(session_status() == PHP_SESSION_NONE) session_start();
		session_regenerate_id(true);
    }
}
/**
 * @package SOY2.SOY2Action
 */
class SOY2ActionSessionBase{
	private $_hash = array();
	function setAttribute($key,$value){
		$this->_hash[$key] = soy2_serialize($value);
		if(is_null($value)){
			unset($this->_hash[$key]);
		}
	}
	function getAttribute($key){
		return (isset($this->_hash[$key])) ? soy2_unserialize($this->_hash[$key]) : null;
	}
	function clearAttributes(){
		$this->_hash = array();
	}
	function getAttributeKeys(){
		return array_keys($this->_hash);
	}
}
/**
 * @package SOY2.SOY2Action
 */
class SOY2UserSession extends SOY2ActionSessionBase{
	private $isAuthenticated = array();
	private $credentials = array();
	function setAuthenticated($key,$flag = null){
		if(is_null($flag) && is_bool($key)){
			$flag = $key;
			$key = "default";
		}
		/**
		 * 以前のハッシュでない$isAuthenticatedがセッションに残っていた時のため
		 * 旧バージョンでのログイン中にファイルを入れ替えるとsetAuthenticatedをしても値が変わらなかった
		 */
		if(!is_array($this->isAuthenticated)){
			$this->isAuthenticated = array();
		}
		$this->isAuthenticated[$key] = $flag;
	}
	function getAuthenticated($key = null){
		if(is_null($key))$key = "default";
		return (isset($this->isAuthenticated[$key])) ? $this->isAuthenticated[$key] : false;
	}
	function addCredential(){
		$args = func_get_args();
		foreach($args as $key => $value){
			$this->credentials[$key] = $args[$key];
		}
	}
	function hasCredential($key){
		return (in_array($key,$this->credentials)) ? true : false;
	}
	function removeCredential($key){
		if(!isset($this->credentials[$key]))return;
		$this->credentials[$key] = null;
		unset($this->credentials[$key]);
	}
	function clearCredentials(){
		$this->credentials = array();
	}
}
/**
 * @package SOY2.SOY2Action
 */
class SOY2FlashSession extends SOY2ActionSessionBase{
	private $isFlash = 0;
	function checkFlash(){
		$this->isFlash++;
		if($this->isFlash >= 2){
			$this->clearAttributes();
			$this->resetFlashCounter();
		}
	}
	function resetFlashCounter(){
		$this->isFlash = 0;
	}
	/**
	 * reset all flash session
	 */
	function reset($array = null){
		$this->clearAttributes();
    	$this->resetFlashCounter();
    	if(is_array($array)){
	    	foreach($array as $key => $value){
	    		$this->setAttribute($key,$value);
	    	}
    	}
	}
}
/* SOY2DAO/SOY2DAO.php */
/**
 * @package SOY2.SOY2DAO
 * SOY2DAO全般の設定をするSingletonクラス
 *
 * @author Miyazawa
 */
class SOY2DAOConfig{
	var $type;
	var $dsn;
	var $user = '';
	var $pass = '';
	var $daoDir = "dao/";
	var $entityDir = "entity/";
	var $daoCacheDir;	//Daoのキャッシュはディフォルトは行わない
	var $event = array();
	/*
	 * PDOのDSN prefixから末尾の:を取り除いた値
	 */
	const DB_TYPE_MYSQL = "mysql";
	const DB_TYPE_SQLITE = "sqlite";
	const DB_TYPE_POSTGRES = "pgsql";
	/*
	 * オプション
	 * limit_query … limit句を使うかどうか(boolean)
	 * keep_statement … statementのキャッシュを強制にする
	 * connection_failure … throw or abort
	 * cache_prefix … キャッシュファイルの先頭に付加する文字列
	 * use_pconnect … 持続的接続を使うかどうか(boolean) PDO::ATTR_PERSISTENT => true
	 */
	var $options = array();
	/*
	 * テーブル名マッピング
	 */
	var $tableMappings = array();
	/**
	 * Constructor
	 */
	private function __construct(){}
	/**
	 * @return SOY2DAOConfig
	 */
	private static function &getInstance(){
		static $_static;
		if(!$_static)$_static = new SOY2DAOConfig();
		return $_static;
	}
	public static function Dsn($dsn = null){
		$config =& self::getInstance();
		$res = $config->dsn;
		if($dsn){
			$config->dsn = $dsn;
			$config->type = substr($dsn,0,strpos($dsn,":"));
		}
		return $res;
	}
	public static function user($user = null){
		$config =& self::getInstance();
		$res = $config->user;
		if($user){
			$config->user = $user;
		}
		return $res;
	}
	public static function pass($pass = null){
		$config =& self::getInstance();
		$res = $config->pass;
		if($pass){
			$config->pass = $pass;
		}
		return $res;
	}
	public static function type(){
		$config =& self::getInstance();
		return $config->type;
	}
	public static function DaoDir($dir = null){
		$config = self::getInstance();
		$res = $config->daoDir;
		if($dir){
			if(substr($dir,strlen($dir)-1) != '/'){
				throw new SOY2DAOException("[SOY2DAO] DaoDir must end by '/'.");
			}
			$config->daoDir = str_replace("\\", "/", $dir);
		}
		return $res;
	}
	public static function EntityDir($dir = null){
		$config = self::getInstance();
		$res = $config->entityDir;
		if($dir){
			if(substr($dir,strlen($dir)-1) != '/'){
				throw new SOY2DAOException("[SOY2DAO] EntityDir must end by '/'.");
			}
			$config->entityDir = str_replace("\\", "/", $dir);
		}
		return $res;
	}
	public static function DaoCacheDir($dir = null){
		$config = self::getInstance();
		$res = $config->daoCacheDir;
		if($dir){
			if(substr($dir,strlen($dir)-1) != '/'){
				throw new SOY2DAOException("[SOY2DAO] EntityDir must end by '/'.");
			}
			$config->daoCacheDir = str_replace("\\", "/", $dir);
		}
		return $res;
	}
	public static function setOption($key, $value = null){
		$config = self::getInstance();
		if($value)$config->options[$key] = $value;
		return (isset($config->options[$key]) ) ? $config->options[$key] : null;
	}
	public static function getOption($key){
		return self::setOption($key);
	}
	public static function setTableMapping($key, $value = null){
		$config = self::getInstance();
		if($value)$config->tableMappings[$key] = $value;
		return (isset($config->tableMappings[$key]) ) ? $config->tableMappings[$key] : $key;
	}
	public static function getTableMapping($key){
		return self::setTableMapping($key);
	}
	/*
	 *
	 * QueryEvent
	 *
	 * SQL発行時にイベント発生
	 *
	 */
	public static function setQueryEvent($function){
		$config = self::getInstance();
		if(!isset($config->event["query"]))$config->event["query"] = array();
		$config->event["query"][] = $function;
	}
	public static function setUpdateQueryEvent($function){
		$config = self::getInstance();
		if(!isset($config->event["updateQuery"]))$config->event["updateQuery"] = array();
		$config->event["updateQuery"][] = $function;
	}
	public static function getQueryEvent(){
		$config = self::getInstance();
		if(!isset($config->event["query"]))$config->event["query"] = array();
		return $config->event["query"];
	}
	public static function getUpdateQueryEvent(){
		$config = self::getInstance();
		if(!isset($config->event["updateQuery"]))$config->event["updateQuery"] = array();
		return $config->event["updateQuery"];
	}
}
/**
 * SOY2DAO
 * DAOImplやDAOの基底となるクラス
 *
 * @package SOY2.SOY2DAO
 * @author Miyazawa
 */
class SOY2DAO{
	protected $_method;	//memcache method name
	protected $_entity;	//memcache entity info
	protected $_query;
	protected $_binds;
	protected $_offset;
	protected $_limit;
	protected $_rowcount;
	protected $_tempQuery = null;
	protected $_statementCache = array();
	protected $_keepStatement;
	protected $_order;
	protected $_responseTime;
	protected $_dsn = null;
	protected $_dbUser = null;
	protected $_dbPass = null;
	/**
	 * 呼び出されたMethodに対応するQueryを返す
	 *
	 * @return QueryのString
	 */
	function getQuery(){
		if(!isset($this->_query[$this->_method])){
			$query = $this->buildQuery($this->_method);
			return $query;
		}
		return $this->_query[$this->_method];
	}
	/**
	 * 呼び出したMethodに対応するQueryを設定する
	 *
	 * @param $sql Query文
	 */
	function setQuery($sql){
		$this->_query[$this->_method] = $sql;
	}
	/**
	 * バインド変数の設定配列を取得する
	 *
	 * @return bind変数配列
	 */
	function getBinds(){
		return $this->_binds;
	}
	/**
	 * SQL文とパラメータ一覧よりバインド配列を返す
	 *
	 * @exception SOY2DAOException バインド変数に設定してある名前がパラメータに無かった
	 *
	 * @param $sql SQL文
	 * @param $binds Methodのパラメータの連想配列
	 *
	 * @return bind配列
	 */
	function buildBinds($sql,$binds){
		if($sql instanceof SOY2DAO_Query){
			$sql = $sql->getQuery();
		}
		$sql = preg_replace("/'[^']*'/","",$sql);
		$regex = ":([a-zA-Z0-9_]*)";
		$tmp = array();
		$result = preg_match_all("/$regex/",$sql,$tmp);
		if(!$result){
			return array();
		}
		$bindArray = array();
		$mapping = $tmp[1];
		foreach($binds as $key => $bind){
			if(is_object($bind)){
				foreach($mapping as $name){
					$method = "get".ucwords($name);
					if(method_exists($bind,$method) && !isset($bindArray[":".$name])){
						$bindArray[":".$name] = $bind->$method();
					}
				}
				unset($mapping[array_search($key,$mapping)]);
			}else{
				if(in_array($key,$mapping) && !isset($bindArray[":".$key])){
					$bindArray[":".$key] = $bind;
				}
			}
		}
		foreach($mapping as $key => $map){
			if(strlen($map) && !array_key_exists(":".$map,$bindArray)){
				throw new SOY2DAOException("バインドするべき変数".$map."が足りません");
			}
		}
		$this->_binds = $bindArray;
		return $bindArray;
	}
	/**
	 * Method名からSQL文を取得する
	 *
	 * @param Method名
	 * @param 永続化しない属性(省略可能)
	 * @param カラム名(省略可能)
	 * @return SQL文
	 */
	function &buildQuery($method,$noPersistents = array(),$columns = array(),$queryType = null){
		if(!isset($this->_query[$method])){
			$this->_query[$method] =
				SOY2DAO_QueryBuilder::buildQuery($method,$this->getEntityInfo(),$noPersistents,$columns,$queryType);
			$this->_query[$method]->replaceTableNames();
		}
		return  $this->_query[$method];
	}
	/**
	 * PDOより帰ってきたArray配列をEntityクラスオブジェクトに変換する
	 *
	 * @param PDOより帰ってきた配列
	 * @return Entityオブジェクト
	 */
	function getObject($row){
		$entityInfo = $this->getEntityInfo();
		$objName = $entityInfo->name;
		$obj = new $objName();
		foreach($row as $key => $value){
			$column = $entityInfo->getColumnByName($key,false);
			if(!$column)continue;
			$propName = $column->prop;
			$method = "set".ucwords($propName);
			$obj->$method($value);
		}
		return $obj;
	}
	/**
	 * EntityInfoクラスオブジェクトを返します
	 *
	 * @return EntityInfoクラスオブジェクト
	 */
	function getEntityInfo(){
		if(!is_object($this->_entity)){
			$this->_entity = unserialize($this->_entity);
		}
		return $this->_entity;
	}
	/**
	 * PDOを取得する
	 *
	 * @return PDOオブジェクト
	 */
	function &getDataSource(){
		return SOY2DAO::_getDataSource($this->getDsn(),$this->getDbUser(),$this->getDbPass());
	}
	function releaseDataSource(){
		SOY2DAO::_releaseDataSource();
	}
	function clearStatementCache(){
		$this->_statementCache = array();
	}
	public static function &_getDataSource($dsn = null,$user = null, $pass = null){
		static $pdo;
		if(is_null($pdo)){
			$pdo = array();
		}
		$dsn = (is_null($dsn)) ? SOY2DAOConfig::Dsn() : $dsn;
		if(!isset($pdo[$dsn])){
			$user = (is_null($user)) ? SOY2DAOConfig::user() : $user;
			$pass = (is_null($pass)) ? SOY2DAOConfig::pass() : $pass;
			$pdoOptions = array(
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			);
			if(SOY2DAOConfig::getOption("use_pconnect")){
				$pdoOptions[PDO::ATTR_PERSISTENT] = true;
			}
			try{
				$pdo[$dsn] = new PDO($dsn,$user,$pass,$pdoOptions);
			} catch (Exception $e) {
				$event = SOY2DAOConfig::getOption("connection_failure");
				if($event == "throw"){
					throw new SOY2DAOException("Can not get DataSource ({$dsn})", $e);
				}else{
					die("Can not get DataSource ({$dsn}).");
				}
			}
			if(SOY2DAOConfig::type() == SOY2DAOConfig::DB_TYPE_MYSQL){
				if(version_compare(PHP_VERSION, "5.3.6") >= 0 && strpos(SOY2DAOConfig::Dsn(),"charset=") !== false){
				}else{
					try{
						$pdo[$dsn]->exec("set names 'utf8'");
					}catch(Exception $e){
					}
				}
			}
		}
		return $pdo[$dsn];
	}
	public static function _releaseDataSource(){
		$pdo = &self::_getDataSource();
		$pdo = null;
	}
	/**
	 * find
	 */
    public static function find($className,$arguments = array()){
		if(!is_array($arguments))$arguments = array("id" => $arguments);
		SOY2DAOFactory::importEntity($className);
		$daoName = $className . "DAO";
		$dao = SOY2DAOFactory::create($daoName);
		if(empty($arguments) && method_exists($dao,"get")){
			return $dao->get();
		}
    	foreach($arguments as $key => $value){
    		if(method_exists($dao,"getBy" . ucwords($key))){
    			$method = "getBy" . ucwords($key);
    			return $dao->$method($value);
    		}
    	}
    	throw new Exception("not supported");
    }
	/**
	 * SQL文をQueryする
	 *
	 * @exception SOY2DAOException 結果が無いとき
	 *
	 * @param SQL文
	 * @param バインド配列
	 *
	 * @return 結果配列
	 */
	function executeQuery($query,$binds = array(),$keepStatement = false){
		if($query instanceof SOY2DAO_Query){
			if(strlen($this->getOrder())){
				$query->setOrder($this->getOrder());
			}
			$query->replaceTableNames();
			$sql = $query->getQuery();
		}else{
			$sql = $query;
		}
		if(!is_null($this->_keepStatement)){
			$keepStatement = $this->_keepStatement;
		}
		$isUseLimitQuery = false;
		if(SOY2DAOConfig::getOption("limit_query") === true && !is_null($this->_limit)){
			if(!is_null($this->_offset)){
				$sql .= " limit " . (int)$this->_offset . "," . (int)$this->_limit;
			}else{
				$sql .= " limit 0," . (int)$this->_limit;
			}
			$isUseLimitQuery = true;
		}
		if(SOY2DAOConfig::getOption("keep_statement") !== null){
			$keepStatement = (boolean)SOY2DAOConfig::getOption("keep_statement");
		}
		$pdo = $this->getDataSource();
		try{
			$events = SOY2DAOConfig::getQueryEvent();
			foreach($events as $event){
				call_user_func($event,$sql,$binds);
			}
			if($keepStatement){
				if(isset($this->_statementCache[md5($sql)])){
					$stmt = $this->_statementCache[md5($sql)];
				}else{
					$stmt = $pdo->prepare($sql);
					$this->_statementCache[md5($sql)] = $stmt;
				}
			}else{
				$stmt = $pdo->prepare($sql);
			}
			if(!$stmt){
				$e = new SOY2DAOException("The database server cannot successfully prepare the statement. SQL: ".$sql);
				$e->setQuery($sql . "");
				throw $e;
			}
			foreach($binds as $key => $bind){
				$type = PDO::PARAM_STR;
				switch(true){
					case is_null($bind) :
						$type = PDO::PARAM_NULL;
						break;
					case is_int($bind) :
						$type = PDO::PARAM_INT;
						break;
					case is_bool($bind) :
					case is_float($bind) :
					case is_numeric($bind) :
					case is_string($bind) :
					default:
						$type = PDO::PARAM_STR;
						break;
				}
				$stmt->bindParam($key, $binds[$key], $type);
			}
			$start = microtime(true);
			$result = $stmt->execute();
			$this->_responseTime = microtime(true) - $start;
		}catch(Exception $e){
			$e = new SOY2DAOException("Invalid query.",$e);
			$e->setQuery($sql . "");
			throw $e;
		}
		if(!$result){
			$e = new SOY2DAOException("[Failed] Statement->execute. ",$e);
			$e->setQuery($sql . "");
			throw $e;
		}
		$resultArray = array();
		$counter = 0;
		if($isUseLimitQuery){
			while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
				$row = $this->unquoteColumnName($query, $sql, $row);
				$resultArray[] = $row;
				$counter++;
			}
		}else{
			if(!is_null($this->_offset)){
				for($i=0; $i<$this->_offset; ++$i){
					if($stmt->fetch() == false)break;
					$counter++;
				}
			}
			while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
				if(is_null($this->_limit) || $counter < ($this->_offset + $this->_limit)){
					$row = $this->unquoteColumnName($query, $sql, $row);
					$resultArray[] = $row;
				}
				$counter++;
			}
		}
		$this->_rowcount = $counter;
		return $resultArray;
	}
	/**
	 * カラム名についた引用符を外す
	 * SQL文にgroup byがあるかどうかに関わらず常に引用符を外すことにした。
	 * PDOのバグなのか、group byを使うとfetchの返り値でカラム名が引用符ごと返って来てしまう。
	 * as "～"を指定したカラムはちゃんと引用符無しで返ってくる。
	 * viewをselectした場合もカラム名が引用符ごと返ってくる。
	 */
	private function unquoteColumnName($query, $sql, $row){
		if($query instanceof SOY2DAO_Query){
			$_row = array();
			foreach($row as $key => $value){
				$_row[$query->unquote($key)] = $value;
			}
			$row = $_row;
		}
		return $row;
	}
	/**
	 * Update系のQueryを実行する
	 *
	 * @param SQL文
	 * @param バインド変数
	 *
	 * @return 結果
	 */
	function executeUpdateQuery($sql,$binds = array(),$keepStatement = false){
		if($sql instanceof SOY2DAO_Query){
			if(strlen($this->getOrder())){
				$sql->setOrder($this->getOrder());
			}
			$sql->replaceTableNames();
			$this->_tempQuery = $sql;	//Queryを保存
			$sql = $sql->getQuery();
		}
		if(SOY2DAOConfig::getOption("keep_statement") !== null){
			$keepStatement = (boolean)SOY2DAOConfig::getOption("keep_statement");
		}
		if(!is_null($this->_keepStatement)){
			$keepStatement = $this->_keepStatement;
		}
		$pdo = $this->getDataSource();
		if($sql instanceof SOY2DAO_Query){
			$sql = $sql->__toString();
		}
		try{
			$events = SOY2DAOConfig::getUpdateQueryEvent();
			foreach($events as $event){
				call_user_func($event,$sql,$binds);
			}
			if($keepStatement){
				if(isset($this->_statementCache[md5($sql)])){
					$stmt = $this->_statementCache[md5($sql)];
				}else{
					$stmt = $pdo->prepare($sql);
					$this->_statementCache[md5($sql)] = $stmt;
				}
			}else{
				$stmt = $pdo->prepare($sql);
			}
			if($stmt === false){
				throw new SOY2DAOException("The database server cannot successfully prepare the statement. SQL: ".$sql);
			}
			foreach($binds as $key => $bind){
				$type = PDO::PARAM_STR;
				switch(true){
					case is_null($bind) :
						$type = PDO::PARAM_NULL;
						break;
					case is_int($bind) :
						$type = PDO::PARAM_INT;
						break;
					case is_bool($bind) :
					case is_float($bind) :
					case is_numeric($bind) :
					case is_string($bind) :
					default:
						$type = PDO::PARAM_STR;
						break;
				}
				$stmt->bindParam($key, $binds[$key], $type);
			}
			$start = microtime(true);
			$result = $stmt->execute();
			$this->_responseTime = microtime(true) - $start;
		}catch(Exception $e){
			$e = new SOY2DAOException("Invalid query.",$e);
			$e->setQuery($sql . "");
			throw $e;
		}
		return $result;
	}
	function setMethod($method){
		$this->_method = $method;
		$this->_binds = array();
	}
	/**
	 * 最後に挿入したIDを取得
	 */
	function lastInsertId(){
		$pdo = $this->getDataSource();
		if(SOY2DAOConfig::type() != SOY2DAOConfig::DB_TYPE_POSTGRES){
			return $pdo->lastInsertId();
		}else{
			if($this->_tempQuery && $this->_tempQuery instanceof SOY2DAO_Query){
				$sequence = $this->_tempQuery->sequence;
				$stmt = $pdo->query("select currval('$sequence') as current_seq_id");
				if(!$stmt)return null;
				while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
					return $row["current_seq_id"];
				}
			}
			return null;
		}
	}
	/**
	 * オフセット指定
	 */
	function setOffset($offset){
		$this->_offset = $offset;
	}
	/**
	 * 件数指定
	 */
	function setLimit($limit){
		$this->_limit = $limit;
	}
	/**
	 * 検索結果の件数取得
	 */
	function getRowCount(){
		return $this->_rowcount;
	}
	/**
	 * トランザクション開始
	 */
	function begin(){
		$this->getDataSource()->beginTransaction();
	}
	/**
	 * ロールバック
	 */
	function rollback(){
		$this->getDataSource()->rollBack();
	}
	/**
	 * コミット
	 */
	function commit(){
		$this->getDataSource()->commit();
	}
	/**
	 * setter keep statement
	 */
	function setKeepStatement($flag){
		$this->_keepStatement = (boolean)$flag;
	}
	/**
	 * テーブル名を取得する
	 */
	function getTableName($key){
		return SOY2DAOConfig::getTableMapping($key);
	}
	/**
	 * setter order
	 */
	function setOrder($order){
		$this->_order = $order;
	}
	/**
	 * getter order
	 */
	function getOrder(){
		return $this->_order;
	}
	/**
	 * setter dsn
	 */
	function setDsn($dsn){
		$this->_dsn = $dsn;
	}
	/**
	 * getter dsn
	 */
	function getDsn(){
		return $this->_dsn;
	}
	function getDbUser() {
		return $this->_dbUser;
	}
	function setDbUser($dbUser) {
		$this->_dbUser = $dbUser;
	}
	function getDbPass() {
		return $this->_dbPass;
	}
	function setDbPass($dbPass) {
		$this->_dbPass = $dbPass;
	}
	/**
	 * 応答時間を取得
	 */
	function getResponseTime(){
		return $this->_responseTime;
	}
}
/**
 * SOY2DAOから吐き出すException
 */
class SOY2DAOException extends Exception{
	private $pdoException;
	private $query;
	function __construct($msg, Exception $e = null){
		$this->pdoException = $e;
		parent::__construct($msg);
	}
	function getPDOExceptionMessage(){
		if(!$this->pdoException)return "";
		$message = $this->pdoException->getMessage();
		if($this->pdoException instanceof PDOException && !empty($this->pdoException->errorInfo)){
			$message .= "; ".implode(", ", $this->pdoException->errorInfo);
		}
		return $message;
	}
	function getPdoException() {
		return $this->pdoException;
	}
	function setPdoException($pdoException) {
		$this->pdoException = $pdoException;
	}
	function getQuery() {
		return $this->query;
	}
	function setQuery($query) {
		$this->query = $query;
	}
}
/* SOY2DAO/soy2dao/SOY2DAOContainer.class.php */
/**
 * @package SOY2.SOY2DAO
 */
class SOY2DAOContainer{
	private $daos = array();
	private function __construct(){
	}
	public static function get($name,$arguments = array()){
		static $instance;
		if(!$instance){
			$instance = new SOY2DAOContainer;
		}
		return $instance->_get($name,$arguments);
	}
    public static function _get($name,$arguments = array()){
		if(isset($this->daos[$name])){
			$dao  = $this->daos[$name];
		}else{
			$dao = SOY2DAOFactory::create($name,$arguments);
			$this->daos[$name] = $dao;
		}
		foreach($arguments as $key => $value){
			if(method_exists($dao,"set".ucwords($key))){
				$func = "set".ucwords($key);
				$dao->$func($value);
				continue;
			}
		}
		return $dao;
    }
}
/* SOY2DAO/soy2dao/SOY2DAOFactory.class.php */
/**
 * SOY2DAOFactory
 * クラス名からDAOImplを生成する
 *
 * @package SOY2.SOY2DAO
 * @author Miyazawa
 */
class SOY2DAOFactory{
	/**
	 * クラス名を元にオブジェクトをつくりそのインスタンスを返す
	 *
	 * @param $className DAOImplを生成したいDAOクラス名
	 * @return DAOImplクラスオブジェクト
	 */
	public static function create($className,$arguments = array()){
		$className = SOY2DAOFactory::importDAO($className);
		$obj = SOY2DAOFactoryImpl::build($className);
		foreach($arguments as $key => $value){
			if(method_exists($obj,"set".ucwords($key))){
				$func = "set".ucwords($key);
				$obj->$func($value);
				continue;
			}
		}
		return $obj;
	}
	const ANNOTATION_ENTITY = "entity";
	const ANNOTATION_QUERY = "query";
	const ANNOTATION_SQL = "sql";
	const ANNOTATION_RETURN = "return";
	const ANNOTATION_ORDER = "order";
	const ANNOTATION_COLUMNS = "columns";
	const ANNOTATION_GROUP = "group";
	const ANNOTATION_HAVING = "having";
	const ANNOTATION_DISTINCT = "distinct";
	const ANNOTATION_TRIGGER = "trigger";
	const ANNOTATION_FINAL = "final";
	const ANNOTATION_QUERY_TYPE = "query_type";
	const ANNOTATION_TABLE = "table";
	const ANNOTATION_ID = "id";
	const ANNOTATION_NO_PERSISTENT = "no_persistent";	//DBに関係ない属性地はこれを設定
	const ANNOTATION_READ_ONLY = "read_only";			//検索系でのみ使用可能な属性
	const ANNOTATION_COLUMN = "column";
	const ANNOTATION_COLUMN_ALIAS = "alias";
	const ANNOTATION_COLUMN_TYPE = "type";
	const ANNOTATION_INDEX = "index";
	/**
	 * コメントからアノテーションを取得する
	 *
	 * @param $key Annotationのキー
	 * @param $str Annotationの入ったコメント文
	 *
	 * @return $keyに対応するAnnotationが存在すればその値、なければfalse
	 */
	public static function getAnnotation($key,$str){
		$regex = '@'.$key.'\s+(.+)';
		$tmp = array();
		if(!preg_match("/$regex/",$str,$tmp)){
			$regex = '@'.$key;
			if(preg_match("/$regex/",$str)){
				return true;
			}else{
				return false;
			}
		}
		return trim($tmp[1]);
	}
	/**
	 * DAOクラスを読み込む
	 *
	 * @param $className クラス名（パッケージ含む）
	 */
	public static function importDAO($className){
		if(!class_exists($className)){
			$path = $className;
			$tmp = array();
			if(preg_match('/\.?([a-zA-Z0-9_]+$)/',$className,$tmp)){
				$className = $tmp[1];
			}
			if(!class_exists($className)){
				$fullPath = SOY2DAOConfig::DaoDir(). str_replace(".","/",$path).".class.php";
				include($fullPath);
			}
		}
		return $className;
	}
	/**
	 * Entityクラスを読み込む
	 *
	 * @param $className クラス名（パッケージ含む）
	 */
	public static function importEntity($className){
		if(!class_exists($className)){
			$path = $className;
			$tmp = array();
			if(preg_match('/\.?([a-zA-Z0-9_]+$)/',$className,$tmp)){
				$className = $tmp[1];
			}
			if(class_exists($className)){
				return $className;
			}
			$fullPath = SOY2DAOConfig::EntityDir(). str_replace(".","/",$path).".class.php";
			require_once($fullPath);
		}
		return $className;
	}
}
/**
 * SOY2DAOFactoryImpl
 * DAOImplを生成する
 *
 * @package SOY2.SOY2DAO
 * @author Miyazawa
 */
class SOY2DAOFactoryImpl extends SOY2DAOFactory {
	/**
	 * クラス名を元にオブジェクトをつくりそのインスタンスを返す
	 *
	 * @param $className DAOクラス名
	 * @return DAOImplクラスオブジェクト
	 */
	public static function build($className){
		$implClassName = self::getImplClassName($className);
		if(class_exists($implClassName)){
			return new $implClassName();
		}
		$cacheFilePath = self::getDaoCacheFilePath($className);
		$reflection = new ReflectionClass($className);
		if(file_exists($cacheFilePath)
			&& filemtime($cacheFilePath) > filemtime(__FILE__)
			&& filemtime($cacheFilePath) > filemtime($reflection->getFileName())
		){
			include_once($cacheFilePath);
		}
		if(class_exists($implClassName)){
			return new $implClassName();
		}
		$daoComment = $reflection->getDocComment();
		$entityClass = self::getEntityClassName($className,$daoComment);
		$entityClass = SOY2DAOFactory::importEntity($entityClass);
		$entityInfo = self::buildEntityInfomation($entityClass);
		if(!$reflection->isSubclassOf(new ReflectionClass("SOY2DAO"))){
			return $reflection->newInstance();
		}
		$methods = $reflection->getMethods();
		foreach($methods as $method){
			if($method->getDeclaringClass()->getName() != $className)continue;
			$methodStrings[] = self::buildMethod($method,$entityInfo);
		}
		$str  = "class ".$reflection->getName()."Impl extends ".$reflection->getName()."{";
		$str.="\n";
		$str .= 'var $_entity = "'.str_replace('"','\"',serialize($entityInfo)).'";';
		$str.="\n";
		$str .= implode("\n",$methodStrings);
		$str .= "}";
		/*
		$classss = explode("\n",$str);
		foreach($classss as $key => $value){
			echo "$key:\t$value<br>";
		}
		*/
		if(SOY2DAOConfig::DaoCacheDir()){
			$fp = fopen($cacheFilePath,"w");
			$entityReflection = new ReflectionClass($entityClass);
			$import = "<?php if(!class_exists('$entityClass')){ \n"
					 ."include_once(\"".str_replace("\\","/",$entityReflection->getFileName())."\"); \n"
					 ."} \n?>";
			$updateCheck = '<?php $updateDate'." = max(filemtime(\"".str_replace("\\","/",$reflection->getFileName())."\"),filemtime(\"".str_replace("\\","/",$entityReflection->getFileName())."\"));";
			$updateCheck .= 'if($updateDate  < filemtime(__FILE__)){ ?>';
			fwrite($fp,$import);
			fwrite($fp,$updateCheck);
			fwrite($fp,"<?php\n".$str."?>");
			fwrite($fp,"<?php\n } \n?>");
			fclose($fp);
		}
		eval($str);
		$name = $reflection->getName()."Impl";
		return new $name();
	}
	/**
	 * メソッドのReflectionを元に実際の内容を作る
	 *
	 * @param $method ReflectionMethod
	 * @return Methodの内容がかかれたString
	 */
	public static function buildMethod($method,$entityInfo){
		$table = self::getAnnotation(SOY2DAOFactory::ANNOTATION_TABLE,$method->getDocComment());
		$return = self::getAnnotation(SOY2DAOFactory::ANNOTATION_RETURN,$method->getDocComment());
		$queryAnnotation = self::getAnnotation(SOY2DAOFactory::ANNOTATION_QUERY,$method->getDocComment());
		$sqlAnnotation = self::getAnnotation(SOY2DAOFactory::ANNOTATION_SQL,$method->getDocComment());
		$noPersistent = self::getAnnotation(SOY2DAOFactory::ANNOTATION_NO_PERSISTENT,$method->getDocComment());
		$order = self::getAnnotation(SOY2DAOFactory::ANNOTATION_ORDER,$method->getDocComment());
		$column = self::getAnnotation(SOY2DAOFactory::ANNOTATION_COLUMNS,$method->getDocComment());
		$columns = (strlen($column)) ? explode(",",$column) : array();
		$group = self::getAnnotation(SOY2DAOFactory::ANNOTATION_GROUP,$method->getDocComment());
		$having = self::getAnnotation(SOY2DAOFactory::ANNOTATION_HAVING,$method->getDocComment());
		$index = self::getAnnotation(SOY2DAOFactory::ANNOTATION_INDEX,$method->getDocComment());
		$distinct = self::getAnnotation(SOY2DAOFactory::ANNOTATION_DISTINCT,$method->getDocComment());
		$trigger = self::getAnnotation(SOY2DAOFactory::ANNOTATION_TRIGGER,$method->getDocComment());
		$final = self::getAnnotation(SOY2DAOFactory::ANNOTATION_FINAL,$method->getDocComment());
		$queryType = self::getAnnotation(SOY2DAOFactory::ANNOTATION_QUERY_TYPE,$method->getDocComment());
		if($final || $method->isFinal() || $method->isPrivate()){
			return;
		}
		$replacePropertyNameFunction = function($key) use ($entityInfo){
			return $entityInfo->getColumn($key[1])->getName();
		};
		$queryAnnotation = preg_replace_callback('/#+([a-zA-Z0-9_]*)#+/',$replacePropertyNameFunction,$queryAnnotation);
		$group = preg_replace_callback('/#+([a-zA-Z0-9_]*)#+/',$replacePropertyNameFunction,$group);
		$having = preg_replace_callback('/#+([a-zA-Z0-9_]*)#+/',$replacePropertyNameFunction,$having);
		$order = preg_replace_callback('/#+([a-zA-Z0-9_]*)#+/',$replacePropertyNameFunction,$order);
		$noPersistent = preg_replace_callback('/#+([a-zA-Z0-9_]*)#+/',$replacePropertyNameFunction,$noPersistent);
		$noPersistents = (strlen($noPersistent)) ? explode(",",$noPersistent) : array();
		$indexColumn = preg_replace_callback('/#+([a-zA-Z0-9_]*)#+/',$replacePropertyNameFunction,$index);
		$columns = preg_replace_callback('/#+([a-zA-Z0-9_]*)#+/',$replacePropertyNameFunction,$columns);
		$parameters = $method->getParameters();
		$params = array();
		foreach($parameters as $param){
			$str = "";
			if(method_exists($param, "getType")){
				$class = (!is_null($param->getType()) && !$param->getType()->isBuiltin()) ? new ReflectionClass($param->getType()->getName()) : null;
			}else{
				$class = (method_exists($param, "getClass")) ? $param->getClass() : "";	//ReflectionParameter::getClass() is deprecated in PHP8
			}
			if($class){
				$str .= $class->getName()." ";
			}
			if($param->isPassedByReference()){
				$str .= "&";
			}
			$str .= '$'.$param->getName();
			if($param->isDefaultValueAvailable()){
				$defValue = $param->getDefaultValue();
				if(is_null($defValue)){
					$defValue = 'null';
				}else if(!is_numeric($defValue)){
					$defValue = '"'.$defValue.'"';
				}
				$str .= " = " . $defValue;
			}
			$params[] = $str;
		}
		$methodString = array();
		$methodString[] =  "function ".$method->getName()."(".implode(",",$params)."){";
		$methodString[] = '$this->setMethod("'.$method->getName().'");';
		if($sqlAnnotation){
			$methodString[] = '$this->setQuery("'.$sqlAnnotation.'");';
		}
		$methodString[] = '$query = $this->buildQuery($this->_method,'.
				'unserialize(\''.serialize($noPersistents).'\'),'.
				'unserialize(\''.serialize($columns).'\'),' .
				'"'.$queryType.'");';
		if($table)$methodString[] = '$query->table = "'.$table.'";';
		if($queryAnnotation)$methodString[] = '$query->where = "'.$queryAnnotation.'";';
		if($order)$methodString[] = '$query->order = "'.$order.'";';
		if($group)$methodString[] = '$query->group = "'.$group.'";';
		if($having)$methodString[] = '$query->having = "'.$having.'";';
		if($distinct)$methodString[] = '$query->distinct = true;';
		$props = array();
		foreach($method->getParameters() as $key => $refParam){
			$props[] = '"'.$refParam->getName().'" => $'.$refParam->getName();
		}
		$methodString[] = 'if($query instanceof SOY2DAO_Query){ $query->parseExpression(array('.implode(',',$props).')); }';
		$methodString[] = '$this->buildBinds($query,array('.implode(',',$props).'));';
		if($method->isAbstract()){
			$methodString[] = '$query = $this->getQuery();';
			$methodString[] = '$binds = $this->getBinds();';
			if($trigger){
				$triggers = explode(",",$trigger);
				foreach($triggers as $key => $trigger){
					$methodString[] = 'if(method_exists($this,"'.$trigger.'")){';
					if(!strpos("::",$trigger)){
						$methodString[] = 'list($query,$binds) = $this->' . $trigger . '($query,$binds);';
					}
					$methodString[] = '}else{';
					$methodString[] = 'list($query,$binds) = ' . $trigger . '($query,$binds);';
					$methodString[] = '}';
				}
			}
			$returnType = $return;
			if(preg_match('/^column_(.*)$/i',$return,$tmp)){
				$returnType = 'column';
				$returnColumnName = $tmp[1];
			}
			if(preg_match('/^columns_(.*)$/i',$return,$tmp)){
				$returnType = 'columns';
				$returnColumnName = $tmp[1];
			}
			if($returnType == "object"
			|| $returnType == "column"
			|| $returnType == "row"
			){
				$methodString[] = '$oldLimit = $this->_limit;';
				$methodString[] = '$this->setLimit(1);';
				$methodString[] = '$oldOffset = $this->_offset;';
				$methodString[] = '$this->setOffset(0);';
			}
			if(preg_match("/^insert|^create/",strtolower($method->getName())) || $queryType == "insert"){
				$methodString[] = '$result = $this->executeUpdateQuery($query,$binds);';
			}else if(preg_match("/^delete|^remove/",strtolower($method->getName())) || $queryType == "delete"){
				$methodString[] = '$result = $this->executeUpdateQuery($query,$binds);';
			}else if(preg_match("/^update|^save|^write|^reset|^change/",strtolower($method->getName())) || $queryType == "update"){
				$methodString[] = '$result = $this->executeUpdateQuery($query,$binds);';
			}else{
				$methodString[] = '$result = $this->executeQuery($query,$binds);';
			}
			switch($returnType){
				case "id":
					$methodString[] = 'return $this->lastInsertId();';
					break;
				case "object":
					$methodString[] = '$this->setLimit($oldLimit);';
					$methodString[] = '$this->setOffset($oldOffset);';
					$methodString[] = 'if(count($result)<1)throw new SOY2DAOException("[SOY2DAO]Failed to return Object.");';
					$methodString[] = '$obj = $this->getObject($result[0]);';
					$methodString[] = 'return $obj;';
					break;
				case "row":
					$methodString[] = '$this->setLimit($oldLimit);';
					$methodString[] = '$this->setOffset($oldOffset);';
					$methodString[] = 'if(count($result)<1)throw new SOY2DAOException("[SOY2DAO]Failed to return row.");';
					$methodString[] = 'return $result[0];';
					break;
				case "column":
					$methodString[] = '$this->setLimit($oldLimit);';
					$methodString[] = '$this->setOffset($oldOffset);';
					$methodString[] = 'if(count($result)<1)throw new SOY2DAOException("[SOY2DAO]Failed to return column.");';
					$methodString[] = '$row = $result[0];';
					$methodString[] = 'return $row["'.$returnColumnName.'"];';
					break;
				case "array":
					$methodString[] = '$array=array();';
					if($index){
						$methodString[] = 'if(is_array($result)){';
						$methodString[] = 'foreach($result as $row){';
						$methodString[] = '$array[$row["'.$indexColumn.'"]] = $row;';
						$methodString[] = '}';
						$methodString[] = '}';
					}else{
						$methodString[] = '$array = $result;';
					}
					$methodString[] = 'return $array;';
					break;
				case "columns":
					$methodString[] = '$array=array();';
					if($index){
						$methodString[] = 'if(is_array($result)){';
						$methodString[] = 	'foreach($result as $row){';
						$methodString[] = 	'$array[$row["'.$indexColumn.'"]] = $row["'.$returnColumnName.'"];';
						$methodString[] = 	'}';
						$methodString[] = '}';
					}else{
						$methodString[] = 'if(is_array($result)){';
						$methodString[] = 	'foreach($result as $row){';
						$methodString[] = 	'$array[] = $row["'.$returnColumnName.'"];';
						$methodString[] = 	'}';
						$methodString[] = '}';
					}
					break;
				case "list":
				default:
					$methodString[] = '$array = array();';
					$methodString[] = 'if(is_array($result)){';
					$methodString[] = 'foreach($result as $row){';
					if($index){
						$func = "get".ucfirst($index);
						$methodString[] = '$obj = $this->getObject($row);';
						$methodString[] = '$array[$obj->'.$func.'()] = $obj;';
					}else{
						$methodString[] = '$array[] = $this->getObject($row);';
					}
					$methodString[] = '}';
					$methodString[] = '}';
					$methodString[] = 'return $array;';
					break;
			}
		}else{
			$parameters = $method->getParameters();
			$params = array();
			foreach($parameters as $parameter){
				$params[] = '$'.$parameter->getName();
			}
			$methodString[] = "return parent::".$method->getName()."(".implode(",",$params).");";
		}
		$methodString[] = '}';
		return implode("\n",$methodString);
	}
	/**
	 * DAOImplのクラス名を返す
	 */
	private static function getImplClassName($className){
		return $className."Impl";
	}
	/**
	 * DAOImplのキャッシュファイル名を返す
	 */
	private static function getDaoCacheFilePath($className, $extension = ".class.php"){
		$reflection = new ReflectionClass($className);
		return SOY2DAOConfig::DaoCacheDir()
		       .SOY2DAOConfig::getOption("cache_prefix")."dao_cache_".self::getImplClassName($className)
		       ."_".md5($reflection->getFileName())
		       .".class.php";
	}
	/**
	 * DAOクラスに関連付けられたEntityClass名を返す
	 *
	 * @param $className DAOクラス名
	 * @param $daoComment DAOクラスのコメント
	 *
	 * @return Entityクラス名
	 */
	public static function getEntityClassName($className,$daoComment){
		$result = self::getAnnotation(SOY2DAOFactory::ANNOTATION_ENTITY,$daoComment);
		if($result !== false){
			$entity = $result;
		}else{
			$entity = preg_replace('/dao$/i',"",$className);
		}
		return $entity;
	}
	/**
	 * Entityクラス名からEntityInfoクラスオブジェクトを作る
	 *
	 * @param $entity EntityClass名
	 * @return EntityInfoのクラスオブジェクト
	 */
	public static function buildEntityInfomation($entity){
		$reflection = new ReflectionClass($entity);
		$comment = $reflection->getDocComment();
		$entityInfo = new SOY2DAO_Entity();
		$entityInfo->name = $entity;
		$table = self::getAnnotation(self::ANNOTATION_TABLE,$comment);
		$entityInfo->table = (strlen($table)>0) ? $table : $entity;
		$id = self::getAnnotation(self::ANNOTATION_ID,$comment);
		$entityInfo->id = $id;
		$properties = $reflection->getProperties();
		$parent = $reflection->getParentClass();
		while($parent){
			$properties = array_merge($properties,$parent->getProperties());
			$parent = $parent->getParentClass();
		}
		foreach($properties as $property){
			$propertyComment = $property->getDocComment();
			$propName = $property->getName();
			if($propName[0] == "_")continue;
			$noPersistent = self::getAnnotation(self::ANNOTATION_NO_PERSISTENT,$propertyComment);
			if($noPersistent)continue;
			$column = new SOY2DAO_EntityColumn();
			$column->prop = $property->getName();
			$columnAnnotation = self::getAnnotation(self::ANNOTATION_COLUMN,$propertyComment);
			$alias = self::getAnnotation(self::ANNOTATION_COLUMN_ALIAS,$propertyComment);
			if($columnAnnotation === false){
				$column->name = $property->getName();
			}else{
				$column->name = $columnAnnotation;
			}
			if($alias !== false){
				$column->alias = $alias;
			}
			$type = self::getAnnotation(self::ANNOTATION_COLUMN_TYPE,$propertyComment);
			if($type){
				$column->type = $type;
			}
			$id = self::getAnnotation(self::ANNOTATION_ID,$propertyComment);
			$tmp = array();
			switch(true){
				case ($id === false):
					break;
				case preg_match("/^sequence=(.*)/",$id,$tmp):
					$column->sequence = $tmp[1];
				case preg_match("/^identity/",$id):
				default:
					$column->isPrimary = true;
					break;
			}
			$readOnly = (boolean)self::getAnnotation(self::ANNOTATION_READ_ONLY,$propertyComment);
			$column->readOnly = $readOnly;
			$entityInfo->columns[strtolower($column->prop)] = $column;
		}
		$entityInfo->buildReverseColumns();
		return $entityInfo;
	}
}
/* SOY2DAO/soy2dao/SOY2DAO_Entity.class.php */
/**
 * SOY2DAO Entity Class
 *
 * @package SOY2.SOY2DAO
 * @see SOY2DAO_EntityColumn
 * @author Miyazawa
 */
class SOY2DAO_Entity{
	var $name;
	var $table;
	var $id;
	var $columns = array();
	var $reverseColumns = array();	//逆引きテーブル
	/**
	 * @return EntityClassのProperty名を連想配列のキーとし、値にカラム名が入ったArrayを返す
	 * @param readOnlyな属性も取得するかどうか
	 */
	function getColumns($flag = false){
		$array = array();
		foreach($this->columns as $column){
			 if(!$flag && $column->readOnly)continue;
			 $array[strtolower($column->prop)] = $column->name;
		}
		return $array;
	}
	/**
	 * @param $key EntityClassのProperty名
	 * @return EntityClassのProperty名から対応するSOY2DAO_EntityColumnオブジェクトを返す。
	 */
	function getColumn($key){
		$key = strtolower($key);
		return (isset($this->columns[$key])) ? $this->columns[$key] : null;
	}
	/**
	 * カラム名からSOY2DAO_EntityColumnオブジェクトを取得
	 * @param $name カラム名
	 */
	function getColumnByName($name,$isThrow = true){
		$name = strtolower($name);
		if(!isset($this->reverseColumns[$name])){
			if($isThrow){
				trigger_error("[SOY2DAO]".$this->name." does not have $name.");
			}else{
				return null;
			}
		}
		return $this->getColumn(@$this->reverseColumns[$name]);
	}
	/**
	 * 逆引きテーブルを作成
	 */
	function buildReverseColumns(){
		foreach($this->columns as $key => $column){
			$name = ($column->getAlias()) ? $column->getAlias() : $column->getName();
			$name = strtolower($name);
			$this->reverseColumns[$name] = $key;
		}
	}
}
/* SOY2DAO/soy2dao/SOY2DAO_EntityBase.class.php */
/**
 * 保存・削除などを自動化するメソッドを自動で追加
 */
class SOY2DAO_EntityBase {
	/**
	 * permanent me
	 */
    final function save(){
    	$dao = $this->getDAO();
    	if($this->check()){
    		if(strlen($this->getId())>0){
	    		$dao->update($this);
	    	}else{
	    		$id = $dao->insert($this);
	    		$this->setId($id);
	    	}
	    	return $this->getId();
    	}else{
    		return null;
    	}
    }
    /**
     * delete me
     */
    final function delete(){
    	$this->getDAO()->delete($this->getId());
    }
    public static function deleteAll(){
    	eval('$obj = new static;');
    	$dao = $obj->getDAO();
    	$dao->deleteAll();
    }
	/**
     * get by id
     */
    final function get($id = null){
    	if($id){
    		$res = $this->getDAO()->getById($id);
    	}else{
    		$res = $this->getDAO()->getById($this->getId());
    	}
    	return $res;
    }
    private $_dao;
    /**
     * build dao
     */
    final function getDAO(){
    	if(is_null($this->_dao)){
	    	$daoClass = get_class($this) . "DAO";
	    	if(!class_exists($daoClass)){
	    		$ref = new ReflectionClass($this);
	    		$filepath = dirname($ref->getFileName()) . "/" . $daoClass . ".class.php";
	    		if(file_exists($filepath))include_once($filepath);
	    	}
	    	$this->_dao = SOY2DAOFactory::create($daoClass);
    	}
    	return $this->_dao;
    }
	public final function begin(){
		$dao = $this->getDAO();
		$dao->begin();
	}
	public final function commit(){
		$dao = $this->getDAO();
		$dao->commit();
	}
	public final function rollback(){
		$dao = $this->getDAO();
		$dao->rollback();
	}
    function __wakeup(){
    	$this->_dao = null;
    }
}
/* SOY2DAO/soy2dao/SOY2DAO_EntityColumn.class.php */
/**
 * SOY2DAO_EntityColumn
 *
 * @package SOY2.SOY2DAO
 * @author Miyazawa
 */
class SOY2DAO_EntityColumn{
	var $id;	//identity,またはsequence=シーケンス名
	var $name;	//カラム名?
	var $alias;	//変換後のカラム名
	var $prop;
	var $isPrimary;
	var $readOnly;
	var $sequence;
	function getId() {
		return $this->id;
	}
	function setId($id) {
		$this->id = $id;
	}
	function getName() {
		return $this->name;
	}
	function setName($name) {
		$this->name = $name;
	}
	function getAlias() {
		return $this->alias;
	}
	function setAlias($alias) {
		$this->alias = $alias;
	}
	function getProp() {
		return $this->prop;
	}
	function setProp($prop) {
		$this->prop = $prop;
	}
	function getIsPrimary() {
		return $this->isPrimary;
	}
	function setIsPrimary($isPrimary) {
		$this->isPrimary = $isPrimary;
	}
	function getSequence() {
		return $this->sequence;
	}
	function setSequence($sequence) {
		$this->sequence = $sequence;
	}
}
/* SOY2DAO/soy2dao/SOY2DAO_Query.class.php */
/**
 * SOY2DAO_Query
 *
 * @package SOY2.SOY2DAO
 */
class SOY2DAO_Query{
	var $prefix;
	var $table;
	var $sql;
	var $where;
	var $order;
	var $group;
	var $having;	//new!!
	var $distinct;
	var $sequence;
	var $binds = array();
	/*
	 * キーワードと区別するために識別子を囲む引用符
	 * http://dev.mysql.com/doc/refman/5.1/ja/identifiers.html
	 * http://www.postgresql.jp/document/pg825doc/html/sql-syntax-lexical.html
	 * http://www.sqlite.org/lang_keywords.html
	 */
	const IDENTIFIER_QUALIFIER_MYSQL = "`";
	const IDENTIFIER_QUALIFIER_SQLITE = '"';
	const IDENTIFIER_QUALIFIER_POSTGRES = '"';
	/**
	 * @return このオブジェクトが持つ情報を元にSQL文が返る
	 */
	function __toString(){
		switch($this->prefix){
			case "insert":
				$sql =  $this->prefix." into ".$this->quoteIdentifier($this->table)." ".$this->sql;
				if(strlen($this->where)){
					$sql .= " where ".$this->where;
				}
				break;
			case "select":
				$sql =  $this->prefix." ";
				if($this->distinct){
					$sql .= "distinct ";
				}
				$sql .= $this->sql." from ".$this->quoteIdentifier($this->table);
				if(strlen($this->where)){
					$sql .= " where ".$this->where;
				}
				if(strlen($this->group)){
					$sql .= " group by ".$this->group;
				}
				if(strlen($this->having)){
					$sql .= " having ".$this->having;
				}
				if(strlen($this->order)){
					$sql .= " order by ".$this->order;
				}
				break;
			case "update":
				$sql =  $this->prefix." ".$this->quoteIdentifier($this->table)." set ".$this->sql;
				if(strlen($this->where)){
					$sql .= " where ".$this->where;
				}
				break;
			case "delete":
				$sql =  $this->prefix." from ".$this->quoteIdentifier($this->table);
				if(strlen($this->where)){
					$sql .= " where ".$this->where;
				}
				break;
		}
		return $sql;
	}
	/**
	 * where句およびhaving句のPHP式の実行
	 * :を使うときは\でエスケープしておく必要がある
	 */
	function parseExpression($arguments){
		/*
		 * 引数の$argumentsはevalの中で使われている
		 */
		$phpExpression = '/<\?php\s(.*)?\?>/';
		if(preg_match($phpExpression,$this->where,$tmp)){
			$expression = $tmp[1];
			$expression = str_replace("\\:","@:@",$expression);
			$expression = preg_replace("/:([a-zA-Z0-9_]+)/",'$arguments[\'$1\']',$expression);
			$expression = str_replace("@:@",":",$expression);
			$replace = "";
			eval('$replace = '.$expression.";");
			if(!is_string($replace) AND !is_numeric($replace))throw new SOY2DAOException("PHP式の変換に失敗しました。(".$tmp[1].")");
			$this->where = preg_replace($phpExpression,$replace,$this->where);
		}
		if(preg_match($phpExpression,$this->having,$tmp)){
			$expression = $tmp[1];
			$expression = preg_replace("/:([a-zA-Z0-9_]*)/",'$arguments[\'$1\']',$expression);
			$replace = "";
			eval('$replace = '.$expression.";");
			if(!is_string($replace) AND !is_numeric($replace))throw new SOY2DAOException("PHP式の変換に失敗しました。(".$tmp[1].")");
			$this->having = preg_replace($phpExpression,$replace,$this->having);
		}
	}
	/**
	 * テーブル名を変換します。
	 * （使われていない模様）
	 */
	function replaceTableNames(){
		$this->table = preg_replace_callback('/([a-zA-Z_0-9]+)\?/',array($this,'replaceTableName'),$this->table);
		$this->sql = preg_replace_callback('/([a-zA-Z_0-9]+)\?/',array($this,'replaceTableName'),$this->sql);
		$this->where = preg_replace_callback('/([a-zA-Z_0-9]+)\?/',array($this,'replaceTableName'),$this->where);
		$this->having = preg_replace_callback('/([a-zA-Z_0-9]+)\?/',array($this,'replaceTableName'),$this->having);
	}
	function replaceTableName($key){
		return SOY2DAOConfig::getTableMapping($key[1]);
	}
	/**
	 * 識別子を引用符で囲みます
	 * MySQL: ` バッククォート
	 * SQLite, PostgreSQL: " ダブルクォート
	 */
	public function quoteIdentifier($identifier){
		if(strlen(preg_replace("/[a-zA-Z0-9_]+/","",$identifier))>0){
			/*
			 * @table table1 join table2 on (table1.id=table2.subid)
			 * や
			 * @column table1.id
			 * のような記述がされているものは囲まない
			 */
			return $identifier;
		}else{
			switch(SOY2DAOConfig::type()){
				case SOY2DAOConfig::DB_TYPE_MYSQL :
					return self::IDENTIFIER_QUALIFIER_MYSQL . $identifier . self::IDENTIFIER_QUALIFIER_MYSQL;
				case SOY2DAOConfig::DB_TYPE_SQLITE :
					return self::IDENTIFIER_QUALIFIER_SQLITE . $identifier . self::IDENTIFIER_QUALIFIER_SQLITE;
				case SOY2DAOConfig::DB_TYPE_POSTGRES :
					return self::IDENTIFIER_QUALIFIER_POSTGRES . $identifier . self::IDENTIFIER_QUALIFIER_POSTGRES;
				default:
					return $identifier;
			}
		}
	}
	/**
	 * 識別子の引用符を外す
	 */
	public function unquote($value){
		$quote = "";
		switch(SOY2DAOConfig::type()){
			case SOY2DAOConfig::DB_TYPE_MYSQL :
				$quote = self::IDENTIFIER_QUALIFIER_MYSQL;
				break;
			case SOY2DAOConfig::DB_TYPE_SQLITE :
				$quote = self::IDENTIFIER_QUALIFIER_SQLITE;
				break;
			case SOY2DAOConfig::DB_TYPE_POSTGRES :
				$quote = self::IDENTIFIER_QUALIFIER_POSTGRES;
				break;
		}
		if(strlen($quote)>0 && strlen($value)>1 && $value[0]===$quote && $value[strlen($value)-1]===$quote){
			$value = substr($value,1,strlen($value)-2);
		}
		return $value;
	}
	/**
	 * SQL文を生成し、返します。
	 * PHP 5.2.0以前では__toStringが呼ばれないのでこちらを使用してください。
	 *
	 * @return string SQL文
	 */
	function getQuery(){
		return $this->__toString();
	}
	function getPrefix() {
		return $this->prefix;
	}
	function setPrefix($prefix) {
		$this->prefix = $prefix;
	}
	function getTable() {
		return $this->table;
	}
	function setTable($table) {
		$this->table = $table;
	}
	function getSql() {
		return $this->sql;
	}
	function setSql($sql) {
		$this->sql = $sql;
	}
	function getWhere() {
		return $this->where;
	}
	function setWhere($where) {
		$this->where = $where;
	}
	function getOrder() {
		return $this->order;
	}
	function setOrder($order) {
		$this->order = $order;
	}
	function getGroup() {
		return $this->group;
	}
	function setGroup($group) {
		$this->group = $group;
	}
	function getHaving() {
		return $this->having;
	}
	function setHaving($having) {
		$this->having = $having;
	}
	function getDistinct() {
		return $this->distinct;
	}
	function setDistinct($distinct) {
		$this->distinct = $distinct;
	}
	function getSequence() {
		return $this->sequence;
	}
	function setSequence($sequence) {
		$this->sequence = $sequence;
	}
	function getBinds() {
		return $this->binds;
	}
	function setBinds($binds) {
		$this->binds = $binds;
	}
}
/* SOY2DAO/soy2dao/SOY2DAO_QueryBuilder.class.php */
/**
 * SOY2DAO_QueryBuilder
 *
 * @package SOY2.SOY2DAO
 * @author Miyazawa
 */
class SOY2DAO_QueryBuilder{
	/**
	 * DAOのメソッド名と、EntityクラスのアノテーションなどからSOY2DAO_Queryオブジェクトを作る
	 * メソッド名からおのおの作るQuery文のbuilderへ処理を渡す
	 *
	 * @param $methodName DAOクラスにあるメソッド名
	 * @param $entityInfo EntityClassのAnnotationなどの情報
	 * @param $noPersistents 無視するカラム
	 * @param $columns 使うカラム
	 * @param $queryType タイプ
	 * @return SOY2DAO_Query
	 */
	public static function buildQuery($methodName,$entityInfo,$noPersistents = array(),$columns = array(),$queryType = null){
		if(preg_match("/^insert|^create/",$methodName) || $queryType == "insert"){
			return SOY2DAO_InsertQueryBuilder::build($methodName,$entityInfo,$noPersistents,$columns);
		}
		if(preg_match("/^delete|^remove/",$methodName) || $queryType == "delete"){
			return SOY2DAO_DeleteQueryBuilder::build($methodName,$entityInfo,$noPersistents,$columns);
		}
		if(preg_match("/^update|^save|^write|^reset|^change/",$methodName) || $queryType == "update"){
			return SOY2DAO_UpdateQueryBuilder::build($methodName,$entityInfo,$noPersistents,$columns);
		}
		return SOY2DAO_SelectQueryBuilder::build($methodName,$entityInfo,$noPersistents,$columns);
	}
	/**
	 * @return SOY2DAO_Query
	 */
	protected static function build($methodName,$entityInfo,$noPersistents,$columns){
		return new SOY2DAO_Query();
	}
}
/* SOY2DAO/soy2dao/SOY2DAO_QueryBuilder_DeleteQueryBuilder.class.php */
/**
 * SOY2DAO_DeleteQueryBuilder
 * delete文のQueryオブジェクトを作る
 *
 * @package SOY2.SOY2DAO
 * @author Miyazawa
 */
class SOY2DAO_DeleteQueryBuilder extends SOY2DAO_QueryBuilder{
	/**
	 * DAOのメソッド名と、EntityクラスのアノテーションなどからSOY2DAO_Queryオブジェクトを作る
	 *
	 * @param $methodName DAOクラスにあるメソッド名
	 * @param $entityInfo EntityClassのAnnotationなどの情報
	 *
	 * @return SOY2DAO_Query
	 */
	protected static function build($methodName,$entityInfo,$noPersistents,$columns){
		$query = new SOY2DAO_Query();
		$query->prefix = "delete";
		$query->table = $entityInfo->table;
		$columns = $entityInfo->getColumns();
		if(preg_match('/By([a-zA-Z0-9_]*)$/',$methodName,$tmp)){
			$param = $tmp[1];
			$column = $entityInfo->getColumn($param);
			if($column){
				$query->where = $query->quoteIdentifier($column->name)." = :{$column->prop}";
			}
		}else{
			foreach($columns as $key => $value){
				$column = $entityInfo->getColumn($key);
				if($column->isPrimary){
					$query->where = $query->quoteIdentifier($column->name)." = :{$column->prop}";
				}
			}
		}
		return $query;
	}
}
/* SOY2DAO/soy2dao/SOY2DAO_QueryBuilder_InsertQueryBuilder.class.php */
/**
 * SOY2DAO_InsertQueryBuilder
 * insert文のQueryオブジェクトを作る
 *
 * @package SOY2.SOY2DAO
 * @author Miyazawa
 */
class SOY2DAO_InsertQueryBuilder extends SOY2DAO_QueryBuilder{
	/**
	 * DAOのメソッド名と、EntityクラスのアノテーションなどからSOY2DAO_Queryオブジェクトを作る
	 *
	 * @param $methodName DAOクラスにあるメソッド名
	 * @param $entityInfo EntityClassのAnnotationなどの情報
	 *
	 * @return SOY2DAO_Query
	 */
	protected static function build($methodName,$entityInfo,$noPersistents,$columns){
		$query = new SOY2DAO_Query();
		$query->prefix = "insert";
		$query->table = $entityInfo->table;
		if(empty($columns)){
			$columns = $entityInfo->getColumns();
		}
		$columnString = array();
		foreach($columns as $key => $value){
			$column = $entityInfo->getColumnByName($value);
			if($column->isPrimary && !$column->sequence){
				continue;
			}
			$columnString[] = $query->quoteIdentifier($column->name);
		}
		$sql = "(".implode(",",$columnString).") ";
		$values = array();
		foreach($columns as $key => $value){
			$column = $entityInfo->getColumnByName($value);
			if($column->isPrimary && $column->sequence){
				$values[] = "nextval(".$query->quoteIdentifier($column->sequence).")";
				$query->sequence = $column->sequence;
				continue;
			}
			if($column->isPrimary){
				continue;
			}
			$values[] = ":".$column->prop;
		}
		$sql.= "values(".implode(",",$values).") ";
		$query->sql = $sql;
		return $query;
	}
}
/* SOY2DAO/soy2dao/SOY2DAO_QueryBuilder_SelectQueryBuilder.class.php */
/**
 * SOY2DAO_SelectQueryBuilder
 * Select文のQueryオブジェクトを作る
 *
 * @package SOY2.SOY2DAO
 * @author Miyazawa
 */
class SOY2DAO_SelectQueryBuilder extends SOY2DAO_QueryBuilder{
	/**
	 * DAOのメソッド名と、EntityクラスのアノテーションなどからSOY2DAO_Queryオブジェクトを作る
	 *
	 * @param $methodName DAOクラスにあるメソッド名
	 * @param $entityInfo EntityClassのAnnotationなどの情報
	 *
	 * @return SOY2DAO_Query
	 */
	protected static function build($methodName,$entityInfo,$noPersistents,$columns){
		$query = new SOY2DAO_Query();
		$query->prefix = "select";
		$query->table = $entityInfo->table;
		if(empty($columns)){
			$columns = $entityInfo->getColumns(true);
		}
		$columns = array_map(array($query,"quoteIdentifier"), $columns);
		$query->sql = implode(",",$columns);
		$tmp = array();
		if(preg_match('/By([a-zA-Z0-9_]*)$/',$methodName,$tmp)){
			$param = $tmp[1];
			$column = $entityInfo->getColumn($param);
			if(!is_null($column)){
				$query->where = $query->quoteIdentifier($column->name)." = :{$column->prop}";
			}
		}
		return $query;
	}
}
/* SOY2DAO/soy2dao/SOY2DAO_QueryBuilder_UpdateQueryBuilder.class.php */
/**
 * SOY2DAO_UpdateQueryBuiler
 * Update文のQueryオブジェクトを作る
 *
 * @package SOY2.SOY2DAO
 * @author Miyazawa
 */
class SOY2DAO_UpdateQueryBuilder extends SOY2DAO_QueryBuilder{
	/**
	 * DAOのメソッド名と、EntityクラスのアノテーションなどからSOY2DAO_Queryオブジェクトを作る
	 *
	 * @param $methodName DAOクラスにあるメソッド名
	 * @param $entityInfo EntityClassのAnnotationなどの情報
	 *
	 * @return SOY2DAO_Query
	 */
	protected static function build($methodName,$entityInfo,$noPersistents,$columns){
		$query = new SOY2DAO_Query();
		$query->prefix = "update";
		$query->table = $entityInfo->table;
		if(empty($columns)){
			$columns = $entityInfo->getColumns();
		}
		$sql = array();
		foreach($columns as $key => $value){
			$column = $entityInfo->getColumnByName($value);
			if(in_array($column->prop,$noPersistents)){
				continue;
			}
			if(in_array($column->name,$noPersistents)){
				continue;
			}
			if($column->isPrimary){
				$query->where = $query->quoteIdentifier($column->name)." = :{$column->prop}";
			}else{
				$sql[] = $query->quoteIdentifier($column->name)." = :{$column->prop}";
			}
		}
		$query->sql = implode(",",$sql);
		return $query;
	}
}
/* SOY2Debug/SOY2Debug.class.php */
/**
 * @package SOY2.SOY2Debug
 */
class SOY2Debug {
	/**
	 * デバッグWindowに文字を出力
	 */
	public static function trace(){
		$args = func_get_args();
		$socket = @fsockopen(self::host(),self::port(), $errno, $errstr,1);
		if(!$socket){
			return;
		}
		foreach($args as $var){
			fwrite($socket,var_export($var,true));
		}
		fclose($socket);
	}
	/**
	 * SOY2Debugのポートを設定。ディフォルトは9999
	 */
	public static function port($port = null){
		static $_port;
		if(is_null($_port)){
			$_port = 9999;
		}
		if($port){
			$_port = (int)$port;
		}
		return $_port;
	}
	/**
	 * SOY2Debugのホストを設定。ディフォルトはlocalhost
	 */
	public static function host($host = null){
		static $_host;
		if(is_null($_host)){
			$_host = "127.0.0.1";
		}
		if($host){
			$_host = $host;
		}
		return $_host;
	}
}
/* SOY2HTML/SOY2HTML.php */
/**
 * SOY2HTMLの基底クラス
 * @package SOY2.SOY2HTML
 * @author Miyazawa
 */
class SOY2HTMLBase{
	private $_soy2_classPath;
	protected $_soy2_functions = array();
	protected function getClassPath(){
		if(is_null($this->_soy2_classPath)){
			$reflection = new ReflectionClass(get_class($this));
			$classFilePath = $reflection->getFileName();
			$this->_soy2_classPath = str_replace("\\", "/", $classFilePath);
		}
		return $this->_soy2_classPath;
	}
	/**
	 * パラメータに与えられた関数を実行し、結果を返す
	 *
	 * @param $name 関数名
	 * @param $args パラメータ
	 *
	 * @return 実行された関数の結果
	 *
	 */
	function __call($name,$args){
		/** PHP7.4対策で廃止
		if(method_exists($this,"createAdd") && preg_match('/^add([A-Za-z]+)$/',$name,$tmp) && count($args)>0){
			$class = "HTML" . $tmp[1];
			if(class_exists($class)){
				$id = array_shift($args);
				$arguments  = (count($args)>0 && is_array($args[0])) ? @$args[0] : array();
				$this->createAdd($id,$class,$arguments);
				if(isset($arguments["value"])){
					$this->createAdd($id . "_text","HTMLLabel",array(
						"text" => $arguments["value"]
					));
				}
				if(($name == "addTextarea") && isset($args["text"])){
					$this->createAdd($id . "_text","HTMLLabel",array(
						"text" => $arguments["text"]
					));
				}
				return;
			}
		}
		**/

		if(!$this->functionExists($name) && $name != "HTMLPage" && $name != "WebPage"){
			throw new SOY2HTMLException("Method not found: ".$name);
		}
		$func = $this->_soy2_functions[$name];
		$code = $func['code'];
		$argments = $func['args'];
		$variant = "";
		if(is_array($argments)){
			$argsCnt = count($argments);
			for($i = 0; $i < $argsCnt; ++$i){
				$variant .= $argments[$i].' = $args['.$i.'];';
			}
		}
		return eval($variant.$code.";");
	}
	/**
	 * 関数を追加登録します
	 *
	 * @param $name 関数名
	 * @param $args パラメータ
	 * @param $code 実行内容
	 */
	function addFunction($name,$args,$code){
		$this->_soy2_functions[$name]['args'] = $args;
		$this->_soy2_functions[$name]['code'] = $code;
	}
	function functionExists($name){
		return array_key_exists($name,$this->_soy2_functions);
	}
}
/**
 * 各コンポーネントの基底となるクラス
 *
 * @see SOY2HTMLBase
 * @package SOY2.SOY2HTML
 * @author Miyazawa
 */
abstract class SOY2HTML extends SOY2HTMLBase{
	const HTML_BODY = '_HTML_BODY_';
	const SKIP_BODY = '_SKIP_BODY_';//空要素：処理としては開始タグのみを出力する
	const SOY_BODY  = '_SOY_BODY_';
	const SOY_TYPE = SOY2HTML::HTML_BODY;
	const ENCODING = 'UTF-8';
	protected $tag = "[a-zA-Z_:][a-zA-Z0-9_:.\-]*|!--";//XML対応で_:なども追加
	protected $_soy2_id;
	protected $_soy2_parentId = null;
	protected $_soy2_parent = null;
	protected $_soy2_prefix = "soy";
	protected $_soy2_pageParam = "page";
	protected $_soy2_parentPageParam = "page";
	protected $_soy2_isModified = true;//更新しているかどうかのフラグ
	protected $_soy2_outerHTML;
	protected $_soy2_innerHTML;
	/*
	 * array(属性名 => 属性値, ...)
	 * createInstanceの第2引数で設定した値が入る
	 */
	public $_soy2_attribute = array();
	/*
	 * array(属性名 => 「属性名」に関する情報, ...)
	 * createInstanceの第2引数で設定した値の場合はその属性が真偽値かどうかが入る
	 * テンプレートのHTMLに元から書かれている値もここに入る
	 */
	public $_attribute      = array();
	protected $_soy2_style;//styleは特別扱い
	protected $_soy2_visible = true;
	protected $_skip_end_tag = false;
	protected $_message_properties = array();
	protected $_soy2_permanent_attributes = array();
	abstract function getObject();
	/**
	 * 準備
	 */
	function init(){
	}
	/**
	 * タグに対応する部分を書き換える
	 */
	function execute(){
		if($this->getComponentType() == SOY2HTML::SKIP_BODY){
			return;
		}
		$this->_soy2_innerHTML ='<?php echo $'.$this->_soy2_pageParam.'["'.$this->_soy2_id.'"]; ?>';
	}
	/**
	 * 前置詞を取得
	 */
	function getSoy2Prefix(){
		return $this->_soy2_prefix;
	}
	/**
	 * 前置詞を設定
	 */
	function setSoy2Prefix($prefix){
		$this->_soy2_prefix = $prefix;
	}
	/**
	 * soy:id を登録する
	 */
	function setId($id){
		$this->_soy2_id = $id;
	}
	/**
	 * getter soy:id
	 */
	function getId(){
		return $this->_soy2_id;
	}
	/**
	 * setter parentId
	 */
	function setParentId($id){
		$this->_soy2_parentId = $id;
	}
	/**
	 * getter parentId
	 */
	function getParentId(){
		return $this->_soy2_parentId;
	}
	/**
	 * setter parent
	 */
	function setParentObject($obj){
		$this->_soy2_parent = $obj;
	}
	/**
	 * getter parent
	 */
	function getParentObject(){
		return $this->_soy2_parent;
	}
	/**
	 * setter pageParam
	 */
	function setPageParam($param){
		$this->_soy2_pageParam = $param;
	}
	/**
	 * getter pageParam
	 */
	function getPageParam(){
		return $this->_soy2_pageParam;
	}
	/**
	 * setter ParentPageParam
	 */
	function setParentPageParam($param){
		$this->_soy2_parentPageParam = $param;
	}
	/**
	 * getter ParentPageParam
	 */
	function getParentPageParam(){
		return $this->_soy2_parentPageParam;
	}
	/**
	 * soy:idの存在するタグを登録します
	 * 例:<p soy:id="title"/>ならばp
	 */
	function setTag($tag){
		$this->tag = $tag;
	}
	/**
	 * getter tag
	 */
	function getTag(){
		return $this->tag;
	}
	/**
	 * getter soy_type
	 */
	function getComponentType(){
		$func = function(){
			//PHP5.3でも一応動作する　調査用
			if(is_null($this) || is_string($this)) return "_HTML_BODY_";
			$className = get_class($this);
			return $className::SOY_TYPE;
		};
		return $func();
	}
	/**
	 * setter soy_visible
	 */
	function setVisible($value){
		$this->_soy2_visible = (boolean)$value;
	}
	/**
	 * getter soy_visible
	 */
	function getVisible(){
		return $this->_soy2_visible;
	}
	/**
	 * setter isModified
	 */
	function setIsModified($value){
		$this->_soy2_isModified = $value;
	}
	/**
	 * getter isModified
	 */
	function getIsModified(){
		return $this->_soy2_isModified;
	}
	/**
	 * setter innerHTML
	 */
	function setInnerHTML($innerHTML){
		$this->_soy2_innerHTML = $innerHTML;
	}
	/**
	 * getter innerHTML
	 */
	function getInnerHTML(){
		return $this->_soy2_innerHTML;
	}
	/**
	 * setter outerHTML
	 */
	function setOuterHTML($outerHTML){
		$this->_soy2_outerHTML = $outerHTML;
	}
	/**
	 * getter outerHTML
	 */
	function getOuterHTML(){
		return $this->_soy2_outerHTML;
	}
	function setSkipEndTag($boolean){
		$this->_skip_end_tag = $boolean;
	}
	function getIsSkipEndTag(){
		return $this->_skip_end_tag;
	}
	/**
	 * HTMLをParseして必要なContentを取得する
	 *
	 * @param $content HTMLソースコード
	 */
	function setContent($content){
		list($tag,$line,$innerHTML,$outerHTML,$value,$suffix,$skipendtag) = $this->parse("id",$this->_soy2_id,$content);
		$this->tag = $tag;
		$this->parseAttributes($line);
		$this->_soy2_innerHTML = $innerHTML;
		$this->_soy2_outerHTML = $outerHTML;
		$this->setSkipEndTag($skipendtag);
	}
	/**
	 * @return array(tag,line,innerhtml,outerhtml,value,suffix,skipendtag)
	 */
	function parse($suffix,$value,$content){
		$result = array(
			"tag" => "",
			"line" => "",
			"innerHTML" => "",
			"outerHTML" => "",
			"value" => "",
			"suffix" => "",
			"skipendtag" => false
		);
		if($content instanceof HTMLList_DummyObject) $content = "";
		switch ($this->getComponentType()) {
			case SOY2HTML::HTML_BODY:
				$regex = '/<(('.$this->tag.')[^<>]*\s'.$this->_soy2_prefix.':('.$suffix.')=\"('.$value.')\"\s?[^>]*)>/i';
				$tmp = array();
				if(preg_match($regex,$content,$tmp,PREG_OFFSET_CAPTURE)){
					$start = $tmp[0][1];
					$end = 0;
					$tmpValue = $tmp[4][0];
					$endTag = $tmp[2][0];
					$endPrefix = $this->_soy2_prefix;
					if($endTag != "!--"){
						$endTag = '\/'. $endTag;
					}else{
						$endPrefix = '\/' . $endPrefix;
					}
					if(strpos($tmpValue,"\\") !== false)$tmpValue = str_replace("\\","\\\\",$tmpValue);
					if(strpos($tmpValue,"/") !== false)$tmpValue = str_replace("/","\\/",$tmpValue);
					if(strpos($tmpValue,"*") !== false)$tmpValue = str_replace("*","\\*",$tmpValue);
					if(strpos($tmpValue,"+") !== false)$tmpValue = str_replace("+","\\+",$tmpValue);
					if(strpos($tmpValue,"?") !== false)$tmpValue = str_replace("?","\\?",$tmpValue);
					if(strpos($tmpValue,".") !== false)$tmpValue = str_replace(".","\\.",$tmpValue);
					if(strpos($tmpValue,"-") !== false)$tmpValue = str_replace("-","\\-",$tmpValue);
					$endRegex = '/(<('.$endTag.')[^<>]*\s'.$endPrefix.':'.$suffix.'=\"'.$tmpValue.'\"\s?[^>]*>)/';
					$endRegex_short = strlen($tmpValue) ? '/(<!--[^<>]*\s\/'.$tmpValue.'\s[^>]*-->)/' : "" ;//短縮形：<!-- /entry -->のように書ける
					$line = $tmp[1][0];
					$tag = $tmp[2][0];
					$suffix = $tmp[3][0];
					$value = $tmp[4][0];
					$result["line"] = $line;
					$result["tag"] = $tag;
					$result["suffix"] = $suffix;
					$result["value"] = $value;
					$innerHTML = "";
					$outerHTML = "";
					$line = trim($line);
					if(preg_match('/\/(--)?$/',$line) OR in_array(strtolower($tag),SOY2HTML::getEmptyTagList())){
						$outerHTML = $tmp[0][0];
						$result["skipendtag"] = true;
					}else if(preg_match($endRegex,$content,$tmp2,PREG_OFFSET_CAPTURE)
						|| strlen($endRegex_short) && preg_match($endRegex_short,$content,$tmp2,PREG_OFFSET_CAPTURE,$tmp[1][1])
					){
						$startOffset = $tmp[1][1];
						$endOffset = $tmp2[1][1] + strlen($tmp2[1][0]);
						$outerHTML = substr($content,$startOffset-1, $endOffset - $startOffset + 1);
						$innerHTML = substr($content,$startOffset+strlen($tmp[1][0])+1,$tmp2[1][1]-($startOffset + strlen($tmp[1][0]))-1);
					}else{
						$i = $start + strlen($tmp[0][0]);
						while($i<strlen($content)){
							$buff = $content[$i];
							if($buff === "<" && $content[$i+1] === "/"){
								$buff = substr($content,$i,strlen("</".$tag));
								$end = $i + strlen("</".$tag);
								/*
								 * 同じタグが内部にある場合は
								 * 動作がおかしくなることはありますけど、現状はこれで良いかと。
								 */
								if($buff === "</".$tag){
									while($end<strlen($content)){
										$buff2 = $content[$end];
										$buff .= $buff2;
										$end++;
										if($buff2 == ">"){
											break;
										}
									}
									break;
								}else{
									$buff = $content[$i];
								}
							}
							$innerHTML .= $buff;
							$i++;
						}
						$outerHTML = substr($content,$start,$end - $start);
					}
					$result["innerHTML"] = $innerHTML;
					$result["outerHTML"] = $outerHTML;
				}
				break;
			case SOY2HTML::SKIP_BODY:
				$regex = '/(<(('.$this->tag.')[^<>]*\s'.$this->_soy2_prefix.':('.$suffix.')=\"('.$value.')\"\s?[^>]*\/?)>)/i';
				$tmp = array();
				if(preg_match($regex,$content,$tmp)){
					$result["outerHTML"] = $tmp[1];
					$result["line"] = $tmp[2];
					$result["tag"] = $tmp[3];
					$result["suffix"] = $tmp[4];
					$result["value"] = $tmp[5];
					$result["skipendtag"] = true;
				}
				break;
			case SOY2HTML::SOY_BODY:
				$startRegex = '/(<(('.$this->tag.')[^<>]*\s'.$this->_soy2_prefix.':('.$suffix.')=\"('.$value.')\"\s?[^>]*)>)/';
				$startRegex_comment = '/(<((!--)[^<>]*\s'.$this->_soy2_prefix.':('.$suffix.')=\"('.$value.')\"\s?[^>]*)>)/';
				$tmp1 = array();
				$tmp2 = array();
				if(preg_match($startRegex_comment,$content,$tmp1,PREG_OFFSET_CAPTURE)){
					$endRegex_comment = '/(<(!--)[^<>]*\s?\/'.$this->_soy2_prefix.':'.$suffix.'=\"'.$value.'\"\s?[^>]*>)/';
					$endRegex_comment_short = '/(<(!--)[^<>]*\s?\/'.$value.'\s?[^>]*>)/';
					if(preg_match($endRegex_comment,$content,$tmp2,PREG_OFFSET_CAPTURE)
						|| preg_match($endRegex_comment_short,$content,$tmp2,PREG_OFFSET_CAPTURE,$tmp1[1][1])
					){
						$startOffset = $tmp1[1][1];
						$endOffset = $tmp2[1][1] + strlen($tmp2[1][0]);
						$result["line"] = $tmp1[2][0];
						$result["tag"] = $tmp1[3][0];
						$result["suffix"] = $tmp1[4][0];
						$result["value"] = $tmp1[5][0];
						$result["outerHTML"] = substr($content,$startOffset, $endOffset - $startOffset);
						$result["innerHTML"] = substr($content,$startOffset + strlen($tmp1[1][0]),$tmp2[1][1] - ($startOffset + strlen($tmp1[1][0])));
					}
				}else if(preg_match($startRegex,$content,$tmp1,PREG_OFFSET_CAPTURE)){
					$tag = $tmp1[3][0];
					$endRegex = '/(<\/('.$tag.')[^<>]*\s'.$this->_soy2_prefix.':'.$suffix.'=\"'.$value.'\"\s?[^>]*>)/';
					$endRegex_short = '/(<\/('.$tag.')>)/';
					if(preg_match($endRegex,$content,$tmp2,PREG_OFFSET_CAPTURE)
						 || preg_match($endRegex_short,$content,$tmp2,PREG_OFFSET_CAPTURE,$tmp1[1][1])){
						$startOffset = $tmp1[1][1];
						$endOffset = $tmp2[1][1] + strlen($tmp2[1][0]);
						$result["line"] = $tmp1[2][0];
						$result["tag"] = $tmp1[3][0];
						$result["suffix"] = $tmp1[4][0];
						$result["value"] = $tmp1[5][0];
						$result["outerHTML"] = substr($content,$startOffset, $endOffset - $startOffset);
						$result["innerHTML"] = substr($content,$startOffset + strlen($tmp1[1][0]),$tmp2[1][1] - ($startOffset + strlen($tmp1[1][0])));
					}
				}
				break;
			default:
				break;
		}
		return array($result["tag"],$result["line"],$result["innerHTML"],$result["outerHTML"],$result["value"],$result["suffix"],$result["skipendtag"]);
	}
	/**
	 * 属性の設定
	 * タグ中のsoy:id以外の属性を格納する
	 *
	 * @param $line
	 */
	function parseAttributes($line){
		$regex ='/([a-zA-Z_:][a-zA-Z0-9_:.\-]*)\s*=\s*"([^"]*)"/';
		$tmp = array();
		if(preg_match_all($regex,$line,$tmp)){
			$keys = $tmp[1];
			$values = $tmp[2];
			foreach($keys as $i => $key){
				$key = strtolower($key);
				$value = html_entity_decode($values[$i], ENT_QUOTES, SOY2HTML::ENCODING);
				if(preg_match('/'.$this->_soy2_prefix.':/',$key)){
					$this->_soy2_attribute[$key] = $value;
					$this->setPermanentAttribute($key,$value);
					continue;
				}
				if($key == "style"){
					$this->_attribute[$key] = new SOY2HTMLStyle($value);
					continue;
				}
				$this->_attribute[$key] = $value;
			}
		}
	}
	/**
	 * soy:idを置換した形のcontentを返す
	 *
	 * @param $tag SOY2HTMLオブジェクト
	 * @param $content HTMLテンプレートソース
	 *
	 * @return 置換された形のcontent
	 */
	function getContent(SOY2HTML $tag,$content){
		$in = $tag->_soy2_outerHTML;
		$tag->parseMessageProperty();
		$out = "";
		switch ($tag->getComponentType()) {
			case SOY2HTML::SKIP_BODY:
				$out = $tag->getStartTag();
				break;
			case SOY2HTML::HTML_BODY:
			case SOY2HTML::SOY_BODY:
				$innerHTML = $tag->_soy2_innerHTML;
				if(strlen($innerHTML)){
					$tag->setSkipEndTag(false);
				}
				$out = $tag->getStartTag().$innerHTML.$tag->getEndTag();
				break;
		}
		list($start,$end) = $tag->getVisbleScript();
		$in = str_replace($in,$start.$out.$end,$content);
		$tmpTag = "[a-zA-Z_:][a-zA-Z0-9_:.\-]*|!--";//XML対応で_:なども追加
		$tag->tag = $tmpTag;
		while(true){
			list($tagName,$line,$innerHTML,$outerHTML,$value,$suffix,$skipendtag) = $tag->parse("id",$tag->_soy2_id.'\*',$in);
			if(strlen($tagName)<1){
				return $in;
			}
			$tag->_attribute = array();
			$tag->_soy2_attribute = array();
			$tag->setTag($tagName);
			$tag->parseAttributes($line);
			$tag->setInnerHTML($innerHTML);
			$tag->setOuterHTML($outerHTML);
			$tag->setSkipEndTag($skipendtag);
			$tag->execute();
			$this->set($tag->getId(),$tag);
			$in = $this->getContent($tag,$in);
			$tag->setTag($tmpTag);
		}
	}
	/**
	 * 開始タグ(<p>とか<a href="・・・">とか)を取得する
	 *
	 * @return 開始タグ
	 */
	function getStartTag(){
		if($this->tag == "!--")return '';
		$attributes = array();
		foreach($this->_attribute as $key => $value){
			if(is_object($value)){
				$value = $value->__toString();
			}
			if(!preg_match("/$key=[\"']/i",$value)){
				$value = ' '.$key."=\"".htmlspecialchars((string)$value, ENT_QUOTES, SOY2HTML::ENCODING)."\"";
			}
			$attributes[] = $value;
		}
		$attribute = implode("",$attributes);
		$out = '<'.$this->tag;
		$out .= $attribute;
		if(SOY2HTMLConfig::getOption("output_html")){
		}else{
			if($this->getComponentType() == SOY2HTML::SKIP_BODY OR $this->getIsSkipEndTag()){
				$out .= ' /';
			}
		}
		$out .= '>';
		return $out;
	}
	/**
	 * 終了タグ(</p>とか</a>とか)を取得する
	 *
	 * @return 終了タグ
	 */
	function getEndTag(){
		if($this->getIsSkipEndTag())return '';
		if($this->tag == "!--")return '';
		return '</'.$this->tag.'>';
	}
	/**
	 * 表示非表示を書き換えるタグを取得
	 *
	 * @return array(開始タグ,終了タグ)
	 */
	function getVisbleScript(){
		return array(
			'<?php if(!isset($'.$this->getPageParam().'["'.$this->getId().'_visible"]) || $'.$this->getPageParam().'["'.$this->getId().'_visible"]){ ?>',
			'<?php } ?>'."\n"
		);
	}
	/**
	 * 属性を取得する
	 *
	 * @param $key 属性名
	 *
	 * @return 属性の値
	 */
	function getAttribute($key){
		$key = strtolower($key);
		return (isset($this->_attribute[$key]) && $this->_attribute[$key] !== true) ? $this->_attribute[$key] :
				 (isset($this->_soy2_attribute[$key]) ? $this->_soy2_attribute[$key] : null);
	}
	function getAttributes(){
		return $this->_soy2_attribute;
	}
	/**
	 * 属性を設定する
	 *
	 * @param $key 属性名
	 * @param $value 属性の値
	 * @param $flag 属性が常に存在するかどうか（disabled, readonlyなどはfalse）
	 *
	 */
	function setAttribute($key,$value,$flag = true){
		$key = strtolower($key);
		$this->_attribute[$key] = $flag;
		$this->_soy2_attribute[$key] = $value;
	}
	/**
	 * 属性値を保存する
	 */
	function setPermanentAttribute($key,$value){
		if(!$this->getIsModified())return;
		$this->_soy2_permanent_attributes[$key] = $value;
	}
	/**
	 * 保存した属性値を取得する
	 */
	function getPermanentAttribute($key = null){
		if(is_null($key)){
			return $this->_soy2_permanent_attributes;
		}
		if(isset($this->_soy2_permanent_attributes[$key])){
			return $this->_soy2_permanent_attributes[$key];
		}else{
			return null;
		}
	}
	/**
	 * 属性を消去する
	 */
	function clearAttribute($key){
		$key = strtolower($key);
		$this->_attribute[$key] = null;
		$this->_soy2_attribute[$key] = null;
		unset($this->_attribute[$key]);
		unset($this->_soy2_attribute[$key]);
	}
	/**
	 * スタイルオブジェクトを取得
	 */
	function &getStyle(){
		if(!isset($this->_soy2_attribute['style'])){
			$this->_soy2_attribute['style'] = new SOY2HTMLStyle();
		}
		return $this->_soy2_attribute['style'];
	}
	function setStyle($style){
		if(!$style instanceof SOY2HTMLStyle){
			$style = new SOY2HTMLStyle($style);
		}
		$this->setAttribute("style",$style);
	}
	function addMessageProperty($key,$message){
		$this->_message_properties[$key] = $message;
	}
	/**
	 * MessagePropertyを置き換える
	 */
	function parseMessageProperty(){
		if($this->getIsModified()){
			foreach($this->_message_properties as $key => $message){
				$tmpKey = "@@".$key.";";
				$this->_soy2_innerHTML = str_replace($tmpKey,$message,$this->_soy2_innerHTML);
			}
		}
	}
	/**
	 * 永続化する場合マージするかどうか
	 */
	function isMerge(){
		return false;
	}
	/**
	 * 永続化処理
	 *
	 * @param $id ページのID
	 * @param $obj タグ
	 */
	function set($id,SOY2HTML &$obj,&$page = null){
		if(is_null($page)){
			$page = &WebPage::getPage($this->getParentId());
		}
		$value = $obj->getObject();
		if(isset($page[$id]) && is_array($value) && $obj->isMerge()){
			$page[$id] = array_merge($page[$id],$value);
		}else{
			$page[$id] = $value;
		}
		$attribute = $obj->_soy2_attribute;
		foreach($attribute as $key => $value){
			if(!isset($obj->_attribute[$key]))continue;
			if(is_object($value))$value = $value->__toString();
			$value = (string)$value;
			if(strlen($value)){
				$page[$obj->getId()."_attribute"][$key] = htmlspecialchars($value,ENT_QUOTES,SOY2HTML::ENCODING);
			}else{
				$page[$obj->getId()."_attribute"][$key] = "";
			}
			/*
			 * _soy2_attributeの値で_attributeの値を上書きする
			 */
			if($obj->_attribute[$key] === false){
				$obj->_attribute[$key] = '<?php if($'.$obj->getPageParam().'["'.$obj->getId().'_attribute"]["'.$key.'"]){ ?>' .
				                         ' '.$key.'="<?php echo $'.$obj->getPageParam().'["'.$obj->getId().'_attribute"]["'.$key.'"]; ?>"' .
				                         '<?php } ?>';
			}else{
				$obj->_attribute[$key] = '<?php if(strlen($'.$obj->getPageParam().'["'.$obj->getId().'_attribute"]["'.$key.'"])){ ?>' .
				                         ' '.$key.'="<?php echo $'.$obj->getPageParam().'["'.$obj->getId().'_attribute"]["'.$key.'"]; ?>"' .
				                         '<?php } ?>';
			}
		}
		$page[$obj->getId()."_visible"] = $obj->getVisible();
	}
	/**
	 * 閉じなくても良いHTMLのリスト
	 * アルファベット順で。
	 */
	public static function getEmptyTagList(){
		return array(
			"area",
			"base",
			"basefont",
			"bgsound",
			"br",
			"embed",
			"hr",
			"img",
			"input",
			"link",
			"meta",
			"param"
		);
	}
	/**
	 * HTMLのタグを除去して実体参照をテキストに戻す
	 */
	public static function ToText($html, $encoding = SOY2HTML::ENCODING){
		/*
		 * html_entity_decodeは文字コードの指定が重要
		 * http://jp2.php.net/manual/ja/function.html-entity-decode.php#function.html-entity-decode.notes
		 */
		//preタグ内にある&lt;と&gt;は予め除いておく
		$html = str_replace(array("&lt;", "&gt;"), "", $html);
		return html_entity_decode(strip_tags($html), ENT_QUOTES, $encoding);
	}
}
/**
 * SOY2HTMLConfig
 * SOY2HTMLに関わる設定を行うSingletonクラス
 *
 * @package SOY2.SOY2HTML
 * @author Miyazawa
 */
class SOY2HTMLConfig{
	private function __construct(){}
	private $cacheDir = "cache/";
	private $pageDir = "pages/";
	private $templateDir = null;
	private $lang = "";	//言語。ディフォルトは空
	private $layoutDir = "layout/";
	/**
	 * cache_prefix … キャッシュファイルの先頭に付加する文字列
	 * output_html … true/false. HTML形式で出力する。デフォルトはXHTML形式（空要素のタグが/>）。
	 */
	private $options = array();
	private static function &getInstance(){
		static $_static;
		if(!$_static){
			$_static = new SOY2HTMLConfig();
		}
		return $_static;
	}
	public static function CacheDir($dir = null){
		$config = self::getInstance();
		if($dir){
			if(substr($dir,strlen($dir)-1) != '/'){
				throw new SOY2HTMLException("[SOY2HTML]CacheDir must end by '/'.");
			}
			$config->cacheDir = str_replace("\\", "/", $dir);
		}
		return $config->cacheDir;
	}
	public static function PageDir($dir = null){
		$config = self::getInstance();
		if($dir){
			if(substr($dir,strlen($dir)-1) != '/'){
				throw new SOY2HTMLException("[SOY2HTML]PageDir must end by '/'.");
			}
			$config->pageDir = str_replace("\\", "/", $dir);
		}
		return $config->pageDir;
	}
	public static function TemplateDir($dir = null){
		$config = self::getInstance();
		if($dir){
			if(substr($dir,strlen($dir)-1) != '/'){
				throw new SOY2HTMLException("[SOY2HTML]TemplateDir must end by '/'.");
			}
			$config->templateDir = str_replace("\\", "/", $dir);
		}
		return $config->templateDir;
	}
	public static function LayoutDir($dir = null){
		$config = self::getInstance();
		if($dir){
			if(substr($dir,strlen($dir)-1) != '/'){
				throw new SOY2HTMLException("[SOY2HTML]Layout Dir must end with '/'.");
			}
			$config->layoutDir = str_replace("\\", "/", $dir);
		}
		return $config->layoutDir;
	}
	/**
	 * SOY2HTMLの言語を設定する
	 */
	public static function Language($lang = null){
		$config = self::getInstance();
		if($lang){
			$config->lang = $lang;
		}
		return $config->lang;
	}
	/**
	 * オプション設定
	 */
	public static function setOption($key, $value = null){
		$config = self::getInstance();
		if($value)$config->options[$key] = $value;
		return (isset($config->options[$key]) ) ? $config->options[$key] : null;
	}
	/**
	 * オプション取得
	 */
	public static function getOption($key){
		return self::setOption($key);
	}
}
/**
 * SOY2HTMLクラスから派生するオブジェクトを生成するFactoryクラス
 *
 * @package SOY2.SOY2HTML
 * @author Miyazawa
 */
class SOY2HTMLFactory extends SOY2HTMLBase{
	/**
	 * 指定したクラス名と属性値から対応するクラスのインスタンスを生成し、返す
	 *
	 * @param $className クラスの名前
	 * @param $attributes 属性の配列
	 *
	 * @return クラスのインスタンス
	 */
	public static function &createInstance($className,$attributes = array()){
		if(!class_exists($className)){
			try{
				self::importWebPage($className);
			}catch(SOY2HTMLException $e){
				throw new SOY2HTMLException("[SOY2HTML]Class ".$className. " is undefined.");
			}
		}
		$tmp = array();
		preg_match('/\.([a-zA-Z0-9_]+$)/',$className,$tmp);
		if(count($tmp)){
			$className = $tmp[1];
		}
		if(isset($attributes['arguments'])){
			$class = new $className($attributes['arguments']);
			$attributes['arguments'] = null;
			unset($attributes['arguments']);
		}else{
			$class = new $className();
		}
		if(is_array($attributes)){
			foreach($attributes as $key => $value){
				if($key == "id"){
					$class->setAttribute($key,$value);
					continue;
				}
				if(strpos($key,"attr:") !== false){
					$key = substr($key,5);
					$class->setAttribute($key,$value);
					continue;
				}
				if(method_exists($class,"set".ucwords($key))  || $class->functionExists("set".ucwords($key))){
					$func = "set".ucwords($key);
					$class->$func($value);
					continue;
				}
				if(stristr($key,':function')){
					$key = trim($key);
					$funcName = str_replace(strstr($key,":function"),"",$key);
					$argsRegex = '/:function\s*\((.*)\)$/';
					$tmp = array();
					if(preg_match($argsRegex,$key,$tmp)){
						$args = explode(",",$tmp[1]);
					}else{
						continue;
					}
					$code = $value;
					$class->addFunction($funcName,$args,$code);
					continue;
				}
				$class->setAttribute($key,$value);
			}
		}

		return $class;
	}
	/**
	 * クラス名から対応するWebPageオブジェクトのファイルをインポートする１
	 *
	 * @param $className クラス名
	 * @exception SOY2HTMLException ファイルが存在しないとき
	 */
	public static function importWebPage($className){
		if(self::pageExists($className) == false){
			throw new SOY2HTMLException();
		}
		$pageDir = SOY2HTMLConfig::PageDir();
		$path = str_replace(".","/",$className);
		$extension = ".class.php";
		include_once($pageDir.$path.$extension);
	}
	public static function pageExists($className){
		$pageDir = SOY2HTMLConfig::PageDir();
		$path = str_replace(".","/",$className);
		$extension = ".class.php";
		if(defined("SOY2HTML_AUTO_GENERATE") && SOY2HTML_AUTO_GENERATE == true && !file_exists($pageDir.$path.$extension) && file_exists($pageDir.$path.".html")){
			self::generateWebPage($className,$pageDir.$path);
		}
		if(!file_exists($pageDir.$path.$extension)){
			return false;
		}
		$tmp = array();
		preg_match('/\.([a-zA-Z0-9_]+$)/',$className,$tmp);
		if(count($tmp)){
			$className = $tmp[1];
		}
		return $className;
	}
	private static function generateWebPage($className,$path){
		$templatePath = $path . ".html";
		$fullPath = $path . ".class.php";
		$dirpath = dirname($fullPath);
		while(file_exists($dirpath) == false){
			if(!mkdir($dirpath))return;
			$dirpath = dirname($dirpath);
		}
		$docComment = array();
		$docComment[] = "/**";
		$docComment[] = " * @class $className";
		$docComment[] = " * @date ".date("c");
		$docComment[] = " * @author SOY2HTMLFactory";
		$docComment[] = " */ ";
		$tmp = array();
		preg_match('/\.([a-zA-Z0-9_]+$)/',$className,$tmp);
		if(count($tmp)){
			$tmpClassName = $tmp[1];
		}else{
			$tmpClassName = $className;
		}
		$class = array();
		$class[] = "class ".$tmpClassName." extends WebPage{";
		$class[] = "	";
		$class[] = '	function '.$tmpClassName.'(){';
		$class[] = "		parent::__construct();";
		$soyIds = array();
		$tmpSoyIds = array();
		$templates = file($templatePath);
		$regex = '/<([^>^\s]*)[^>]*(\/)?soy:id=\"([a-zA-Z][a-zA-Z0-9_]+)\"\s?[^>]*>/i';
		foreach($templates as $str){
			if(!preg_match($regex,$str,$tmp))continue;
			$tag = $tmp[1];
			$isEnded = (boolean)(strlen($tmp[2]) OR $tag[0] == "/");
			$soyId = $tmp[3];
			if($isEnded && isset($tmpSoyIds[$soyId])){
				$childSoyIds = array();
				$tmpKeys = array_keys($tmpSoyIds);
				$tmpKeys = array_reverse($tmpKeys);
				foreach($tmpKeys as $value){
					if($value == $soyId){
						$tmpSoyIds[$soyId]["child"] = array_reverse($childSoyIds);
						$soyIds += array_reverse($tmpSoyIds);
						$tmpSoyIds = array();
						break;
					}
					$childSoyIds[$value] = $tmpSoyIds[$value];
					unset($tmpSoyIds[$value]);
				}
				continue;
			}
			$tmpSoyIds[$soyId] = array(
				"tag" => $tag,
				"child" => array()
			);
		}
		list($result,$classes) = self::generateCreateAdd($soyIds);
		$class[] = implode("\n\t\t",$result);
		$class[] = "	}";
		$class[] = "}";
		$class[] = "";
		$class[] = implode("\n",$classes);
		file_put_contents($fullPath,"<?php \n".implode("\n",$docComment) ."\n". implode("\n",$class)."\n?>");
	}
	private static function generateCreateAdd($soyIds,$className = "HTMLLabel"){
		$keys = array_keys($soyIds);
		$script = array();
		$classes = array();
		foreach($keys as $key){
			$className = "HTMLLabel";
			$createKey = array("text");
			$script[] = '';
			if($soyIds[$key]["tag"] == "input"){
				$className = "HTMLInput";
				$createKey = array(
					"name" => $key,
					"value" => ""
				);
			}
			if($soyIds[$key]["tag"] == "select"){
				$className = "HTMLSelect";
				$createKey = array(
					"name" => $key,
					"options" => array(),
					"selected" => ""
				);
			}
			if($soyIds[$key]["tag"] == "textarea"){
				$className = "HTMLTextArea";
				$createKey = array(
					"name" => $key,
					"value" => ""
				);
			}
			if(preg_match('/_link$/',$key)){
				$className = "HTMLLink";
				$createKey = array("link");
			}
			if(preg_match('/_form$/',$key)){
				$className = "HTMLForm";
				list($tmpScript,$tmpClass) = self::generateCreateAdd($soyIds[$key]["child"]);
				$script[] = '$this->createAdd("'.$key.'","'.$className.'");';
				$script = array_merge($script,$tmpScript);
				$classes = array_merge($classes,$tmpClass);
				continue;
			}
			if(preg_match('/_list$/',$key)){
				$className = str_replace("_list","List",ucwords($key));
				list($tmpScript,$tmpClass) = self::generateCreateAdd($soyIds[$key]["child"]);
				$script[] = '$this->createAdd("'.$key.'","'.$className.'",array(';
				$script[] = "\t".'"list" => array()';
				$script[] = '));';
				$classes[] = '';
				$classes[] = '/**';
				$classes[] = ' * @class '.$className;
				$classes[] = ' * @generated by SOY2HTML';
				$classes[] = ' */';
				$classes[] = 'class '.$className.' extends HTMLList{';
				$classes[] = "\t".'protected function populateItem($entity){';
				$classes[] = "\t\t".implode("\n\t\t",$tmpScript);
				$classes[] = "\t".'}';
				$classes[] = '}';
				$classes = array_merge($classes,$tmpClass);
				continue;
			}
			list($tmpScript,$tmpClass) = self::generateCreateAdd($soyIds[$key]["child"]);
			$script[] = '$this->createAdd("'.$key.'","'.$className.'",array(';
			foreach($createKey as $tmpCreateKey => $defaultValue){
				if(is_numeric($tmpCreateKey)){
					$tmpCreateKey = $defaultValue;
					$defaultValue = "";
				}
				if(is_string($defaultValue)){
					$defaultValue = '"'.$defaultValue.'"';
				}
				if(is_array($defaultValue)){
					$defaultValue = 'array()';
				}
				if(!strlen($defaultValue))$defaultValue = '""';
				$script[] = "\t".'"'.$tmpCreateKey.'" => '.$defaultValue.',';
			}
			$script[] = '));';
			$script = array_merge($script,$tmpScript);
			$classes = array_merge($classes,$tmpClass);
		}
		return array($script,$classes);
	}
}
/**
 * SOY2HTMLが出力するSOY2HTMLException
 */
class SOY2HTMLException extends Exception{}
/* SOY2HTML/SOY2HTMLComponents/HTMLBase.php */
/**
 * @package SOY2.SOY2HTML
 */
class SOYBodyComponentBase extends SOY2HTML{
	protected $_components = array();
	protected $_tmpList = array();
	protected $_childSoy2Prefix  = "soy";
	const SOY_TYPE = SOY2HTML::SOY_BODY;
    function add($id,$obj){
    	$obj->setId($id);
    	$obj->setParentObject($this);
    	$obj->init();
    	$this->_components[$id] = $obj;
    }
    /**
	 * コンポーネントクラスを指定してadd
	 *
	 * @param $id SoyId
	 * @param $className クラス名
	 * @param $array = array()　setter injection
	 * @see HTMLPage.add
	 */
	function createAdd($id,$className,$array = array()){
		if(!isset($array["soy2prefix"]) && $this->_childSoy2Prefix) {
			if(!is_array($array)) $array = array();
			$array["soy2prefix"] = $this->_childSoy2Prefix;
		}
		$this->add($id,SOY2HTMLFactory::createInstance($className,$array));
	}
	function getStartTag(){
    	return '<?php $'.$this->getId().' = $'.$this->getPageParam().'["'.$this->getId().'"]; ?>'.parent::getStartTag();
    }
	function getObject(){
    	return $this->_tmpList;
    }
	function execute(){
		$innerHTML = $this->getInnerHTML();
		$tmpList = array();
		foreach($this->_components as $key => $obj){
			if($obj instanceof HTMLPage){
				$obj->setParentPageParam($this->getId());
    		}
			$obj->setParentId($this->getId());
			$obj->setPageParam($this->getId());
			$obj->setContent($innerHTML);
			$obj->execute();
			$this->set($key,$obj,$tmpList);
			if($innerHTML){
				$innerHTML = $this->getContent($obj,$innerHTML);
			}
		}
		$this->_tmpList = $tmpList;
		$this->setInnerHTML($innerHTML);
	}
	function setChildSoy2Prefix($prefix){
		$this->_childSoy2Prefix = $prefix;
	}
	function isMerge(){
		return true;
	}

	/** PHP7.4対応 SOY2HTMLBaseの__call()の廃止 **/
	function addForm($id, $array=array()){self::createAdd($id, "HTMLForm", $array);}
	function addUploadForm($id, $array=array()){self::createAdd($id, "HTMLUploadForm", $array);}
	function addModel($id, $array=array()){self::createAdd($id, "HTMLModel", $array);}
	function addLabel($id, $array=array()){self::createAdd($id, "HTMLLabel", $array);}
	function addImage($id, $array=array()){self::createAdd($id, "HTMLImage", $array);}
	function addLink($id, $array=array()){self::createAdd($id, "HTMLLink", $array);}
	function addActionLink($id, $array=array()){self::createAdd($id, "HTMLActionLink", $array);}
	function addInput($id, $array=array()){
		self::createAdd($id, "HTMLInput", $array);
		self::addText($id, $array);
	}
	function addTextArea($id, $array=array()){
		self::createAdd($id, "HTMLTextArea", $array);
		self::addText($id, $array);
	}
	function addCheckBox($id, $array=array()){
		self::createAdd($id, "HTMLCheckBox", $array);
		self::addText($id, $array);
	}
	function addSelect($id, $array=array()){
		self::createAdd($id, "HTMLSelect", $array);
		self::addText($id, $array);
	}
	function addHidden($id, $array=array()){self::createAdd($id, "HTMLHidden", $array);}
	function addScript($id, $array=array()){self::createAdd($id, "HTMLScript", $array);}
	function addCSS($id, $array=array()){self::createAdd($id, "HTMLCSS", $array);}
	function addCSSLink($id, $array=array()){self::createAdd($id, "HTMLCSSLink", $array);}
	function addText($id, $array=array()){
		$new = array();
		if(isset($array["soy2prefix"]) && strlen($array["soy2prefix"])) $new["soy2prefix"] = $array["soy2prefix"];
		$new["text"] = (isset($array["value"])) ? $array["value"] : null;
		if(!strlen($new["text"]) && isset($array["text"]) && strlen($array["text"])) $new["text"] = $array["text"]; //addTextAreaの場合
		self::createAdd($id. "_text", "HTMLLabel", $new);
	}
	function addList($id, $array=array()){self::createAdd($id, "HTMLList", $array);}
}
/**
 * @package SOY2.SOY2HTML
 */
class SOY2HTMLElement extends SOY2HTML{
	const TEXT_ELEMENT = "_text_element_";
	var $_elements = array();
	var $_innerHTML;
	var $_tag;
	public static function &createElement($tag){
		return SOY2HTMLFactory::createInstance("SOY2HTMLElement",array(
			"elementTag" => $tag
		));
	}
	public static function &createTextElement($text){
		$ele =  SOY2HTMLFactory::createInstance("SOY2HTMLElement",array(
			"elementTag" => SOY2HTMLElement::TEXT_ELEMENT
		));
		$ele->_innerHTML = htmlspecialchars($text, ENT_QUOTES, SOY2HTML::ENCODING);
		return $ele;
	}
	public static function &createHtmlElement($html){
		$ele =  SOY2HTMLFactory::createInstance("SOY2HTMLElement",array(
			"elementTag" => SOY2HTMLElement::TEXT_ELEMENT
		));
		$ele->_innerHTML = $html;
		return $ele;
	}
	function setElementTag($tag){
		$this->_tag = $tag;
	}
	function getStartTag(){
		return "";
	}
	function getEndTag(){
		return "";
	}
	function setAttribute($key,$value,$flag = true){
		$this->_attribute[$key] = $value;
	}
	function getObject(){
		return $this->toHTML();
	}
	function toHTML(){
		$this->tag = $this->_tag;
		if($this->tag == SOY2HTMLElement::TEXT_ELEMENT){
			return $this->_innerHTML;
		}
		$html = SOY2HTML::getStartTag();
		$innerHTML = "";
		foreach($this->_elements as $ele){
			$innerHTML .= $ele->toHTML();
		}
		if(strlen($innerHTML)){
			$html .= $innerHTML;
			$html .= SOY2HTML::getEndTag();
		}else{
			$html = preg_replace('/>$/','/>',$html);
		}
		return $html;
	}
	function appendChild(SOY2HTMLElement &$ele){
		$this->_elements[] = $ele;
	}
}
/**
 * @package SOY2.SOY2HTML
 */
class SOY2HTMLStyle{
	var $_styles = array();
	function __construct($style = ""){
		$styles = explode(";",$style);
		foreach($styles as $str){
			if(!strstr($str,":"))continue;
			$array = explode(":",$str,2);
			$this->_styles[$array[0]] = $array[1];
		}
	}
	function __toString(){
		$style = '';
		foreach($this->_styles as $key => $value){
			if(!strlen($key) OR !strlen($value))continue;
			$style .= "$key:$value;";
		}
		return $style;
	}
	function __set($key, $value){
		//$key = preg_replace_callback('/[A-Z]/',create_function('$word','return \'-\'.strtolower($word[0]);'),$key);
		$key = preg_replace_callback('/[A-Z]/', function($word) use ($key) { return '-'.strtolower($word[0]); }, $key);
		$this->_styles[$key] = $value;
	}
	function __get($key){
		//$key = preg_replace_callback('/[A-Z]/',create_function('$word','return \'-\'.strtolower($word[0]);'),$key);
		$key = preg_replace_callback('/[A-Z]/', function($word) use ($key) { return '-'.strtolower($word[0]); }, $key);
		return $this->_styles[$key];
	}
}
/**
 * @package SOY2.SOY2HTML
 * 何もしないコンポーネント
 */
class HTMLModel extends SOY2HTML{
	const SOY_TYPE = SOY2HTML::HTML_BODY;
	function execute(){}
	function getObject(){
		return "";
	}
}
/* SOY2HTML/SOY2HTMLComponents/HTMLCSS.class.php */
/**
 * @package SOY2.SOY2HTML
 */
class HTMLCSS extends SOY2HTML{
	var $tag = "style";
	const SOY_TYPE = SOY2HTML::HTML_BODY;
	var $text = "";
	function execute(){
		$this->setAttribute("type","text/css");
		parent::execute();
	}
	function setStyle($text){
		$this->text = $text;
	}
	function getObject(){
		return $this->text;//htmlspecialchars((string)$this->text,ENT_QUOTES,SOY2HTML::ENCODING)
	}
}
/* SOY2HTML/SOY2HTMLComponents/HTMLCSSLink.class.php */
/**
 * @package SOY2.SOY2HTML
 */
class HTMLCSSLink extends SOY2HTML{
	var $tag = "link";
	const SOY_TYPE = SOY2HTML::SKIP_BODY;
	var $link;
	function setLink($link){
		$this->link = $link;
	}
	function execute(){
		$this->setAttribute("href",$this->link);
	}
	function getObject(){
		return $this->link;
	}
}
/* SOY2HTML/SOY2HTMLComponents/HTMLForm.class.php */
/**
 * @package SOY2.SOY2HTML
 */
class HTMLForm extends SOYBodyComponentBase{
    var $tag = "form";
    var $action;
    var $_method = "post";
	private $disabled;
    function setTag($tag){
    	throw new SOY2HTMLException("[HTMLForm]タグの書き換えは不可です。");
    }
    function setMethod($method){
    	$this->_method = $method;
    }
    function setAction($action){
    	$this->action = $action;
    }
    function setTarget($target){
    	$this->setAttribute("target",$target);
    }
    function getStartTag(){
    	if(strtolower($this->_method) == "post"){
    		$token = '<input type="hidden" name="soy2_token" value="<?php echo soy2_get_token(); ?>" />';
    		return parent::getStartTag() . $token;
    	}
    	return parent::getStartTag();
    }
    function execute(){
		SOYBodyComponentBase::execute();
		if($this->action){
			$this->setAttribute("action",$this->action);
		}else{
			$this->setAttribute("action",@$_SERVER["REQUEST_URI"]);
		}
		$this->setAttribute('method',$this->_method);
		$disabled = ($this->disabled) ? "disabled" : null;
		$this->setAttribute("disabled",$disabled, false);
    }
    function setOnSubmit($value){
    	if(!preg_match("/^javascript:/i",$value)){
    		$value = "javascript:".$value;
    	}
    	$this->setAttribute("onsubmit",$value);
    }
	function getDisabled() {
		return $this->disabled;
	}
	function setDisabled($disabled) {
		$this->disabled = $disabled;
	}
}
/**
 * @package SOY2.SOY2HTML
 */
class HTMLUploadForm extends HTMLForm{
	function execute(){
		parent::execute();
		$this->setAttribute("enctype","multipart/form-data");
	}
}
/**
 * @package SOY2.SOY2HTML
 */
abstract class HTMLFormElement extends SOY2HTML{
	var $name;
	private $disabled;
	private $readonly;
	function execute(){
		parent::execute();
		$disabled = ($this->disabled) ? "disabled" : null;
		$this->setAttribute("disabled",$disabled, false);
		$readonly = ($this->readonly) ? "readonly" : null;
		$this->setAttribute("readonly",$readonly, false);
	}
	function setName($value){
		$this->name = $value;
		$this->setAttribute("name",$value);
	}
	function getDisabled() {
		return $this->disabled;
	}
	function setDisabled($disabled) {
		$this->disabled = $disabled;
	}
	function getReadonly() {
		return $this->readonly;
	}
	function setReadonly($readonly) {
		$this->readonly = $readonly;
	}
}
/**
 * @package SOY2.SOY2HTML
 */
class HTMLInput extends HTMLFormElement{
	const SOY_TYPE = SOY2HTML::SKIP_BODY;
	var $tag = "input";
	var $value;
	var $type;
	function setValue($value){
		$this->value = $value;
		$this->setAttribute("value",$this->value);
	}
	function execute(){
		parent::execute();
	}
	function getObject(){
		return $this->value;
	}
	function setType($value){
		$this->type = $value;
		$this->setAttribute("type",$this->type);
	}
	function getType(){
		return $this->type;
	}
}
/**
 * @package SOY2.SOY2HTML
 */
class HTMLHidden extends HTMLInput{
	function execute(){
		parent::execute();
		$this->setAttribute("type","hidden");
	}
}
/**
 * @package SOY2.SOY2HTML
 */
class HTMLTextArea extends HTMLFormElement{
	var $tag = "textarea";
	const SOY_TYPE = SOY2HTML::HTML_BODY;
	var $text;
	function setText($value){
		$this->text = $value;
	}
	function setValue($value){
		$this->text = $value;
	}
	function getText(){
		return (string) $this->text;
	}
	function getObject(){
		return "\n".htmlspecialchars($this->getText(),ENT_QUOTES,SOY2HTML::ENCODING);
	}
}
/**
 * HTMLSelect
 * @package SOY2.SOY2HTML
 * 使い方
 * <select soy:id="test_select"></select>
 *
 * $this->createAdd("test_select","HTMLSelect",array(
 * 		"selected" => $selectedvalue,
 * 		"options" => array(
 * 			"りんご","みかん","マンゴー"
 * 		),
 * 		"each" => array(
 * 			"onclick"=>"alert(this.value);"
 * 		),
 * 		"indexOrder" => $boolean,
 * 		"name" => $name
 * ));
 *
 * indexOrderがtrueの場合、またはoptionsに指定した配列が連想配列の場合は
 * <option value="0">りんご</option>
 * <option value="1">みかん</option>
 * <option value="2">マンゴー</option>
 * または
 * <option value="apple">りんご</option>
 * <option value="mandarin">みかん</option>
 * <option value="mango">マンゴー</option>
 * に展開されます。
 *
 * optionsに指定した配列が連想配列で無い場合（かつindexOrderがtrueでない場合）は
 * <option>りんご</option>
 * <option>みかん</option>
 * <option>マンゴー</option>
 * です。
 *
 * optionsを多重配列にすることで<optgroup>を指定できます。
 *
 * selectedを複数指定するときは配列にします。
 */
class HTMLSelect extends HTMLFormElement {
	var $tag = "select";
	const SOY_TYPE = SOY2HTML::HTML_BODY;
	var $options;
	var $selected;//複数指定するときは配列
	private $multiple = false;
	var $indexOrder = false;
	var $property;
	var $each = "";
	function setOptions($options){
		$this->options = $options;
	}
	function setSelected($selected){
		$this->selected = $selected;
	}
	function getMultiple() {
		return $this->multiple;
	}
	function setMultiple($multiple) {
		$this->multiple = $multiple;
	}
	function setIndexOrder(){
		$this->indexOrder = true;
	}
	function setProperty($name){
		$this->property = $name;
	}
	function setEach($each){
		if(is_array($each) && count($each)){
			$attr = array();
			foreach($each as $key => $value){
				$attr[] = htmlspecialchars((string)$key, ENT_QUOTES,SOY2HTML::ENCODING).'="'.htmlspecialchars((string)$value, ENT_QUOTES,SOY2HTML::ENCODING).'"';
			}
			$this->each = implode(" ",$attr);
		}
	}
	function execute(){
		$innerHTML  = $this->getInnerHTML();
		parent::execute();
		$this->setInnerHTML($innerHTML.$this->getInnerHTML());
		$multiple = ($this->multiple) ? "multiple" : null;
		$this->setAttribute("multiple",$multiple, false);
	}
	function getObject(){
		$first = (is_array($this->options) && count($this->options)) ? array_slice($this->options, 0, 1) : array();
		if(is_array(array_shift($first))){
			$twoDimensional = true;
			$isHash = false;
		}else{
			$twoDimensional = false;
			$isHash = (is_array($this->options) && array_keys($this->options) === range(0,count($this->options)-1)) ? false : true;
		}
		if($this->indexOrder){
			$isHash = true;
		}
		$buff = "";
		if($twoDimensional && is_array($this->options) && count($this->options)){
			foreach($this->options as $key => $value){
				if(is_array($value)){
					$key = (string)$key;
					$buff .= '<optgroup label="'.htmlspecialchars((string)$key, ENT_QUOTES,SOY2HTML::ENCODING).'">';
					$buff .= $this->buildOptions($value, $isHash);
					$buff .= '</optgroup>';
				}else{
					$buff .= $this->buildOption($key, $value, $isHash);
				}
			}
		}else{
			$buff .= $this->buildOptions($this->options, $isHash);
		}
		return $buff;
	}
	function buildOptions($options, $isHash){
		$buff = "";
		if(is_array($options) && count($options)){
			foreach($options as $key => $value){
				$buff .= $this->buildOption($key, $value, $isHash);
			}
		}
		return $buff;
	}
	function buildOption($key, $value, $isHash){
		$buff = "";
		$selected = '';
		$key = (string)$key;
		if(is_object($value) && $this->property){
			$propName = $this->property;
			$funcName = "get" . ucwords($propName);
			if(method_exists($value,$funcName)){
				$value = $value->$funcName();
			}else{
				$value = $value->$propName;
			}
		}
		if($isHash || !is_numeric($key)){
			$selected = ($this->selected($key)) ? 'selected="selected"' : '';
		}else{
			$selected = ($this->selected($value)) ? 'selected="selected"' : '';
		}
		$attributes = "";
		if(strlen($selected))   $attributes .= " ".$selected;
		if(strlen($this->each)) $attributes .= " ".$this->each;
		if($isHash || !is_numeric($key)){
			$attributes .= ' value="'.htmlspecialchars((string)$key,ENT_QUOTES,SOY2HTML::ENCODING).'"';
		}
		$buff .= "<option".$attributes.">".htmlspecialchars((string)$value,ENT_QUOTES,SOY2HTML::ENCODING)."</option>";
		return $buff;
	}
	/**
	 * 値がselectedであるかどうか
	 */
	function selected($value){
		if(is_array($this->selected)){
			return in_array($value,$this->selected);
		}else{
			return ($value == $this->selected);
		}
	}
	function setValue($value){
		$this->setSelected($value);
	}
}
/**
 * HTMLCheckBox
 *
 * 使い方１
 * <input type="checkbox" soy:id="soyid" />
 * $this->createAdd("soyid", "HTMLCheckbox", array(
 *  "label" => "LABEL",//<label for="thisid">LABEL</label>が自動的に生成される
 * 	"selected" => true, //or false //checked="checked"生成
 *  "isBoolean" => true, //<input type="hidden" value="0" />生成
 * ));
 *
 * 使い方２
 * <input type="checkbox" soy:id="soyid" id="checkboxid" /><label for="checkboxid">MY LABEL</label>
 * $this->createAdd("soyid", "HTMLCheckbox", array(
 * 	"elementId" => "checkboxid",
 * 	"selected" => true, //or false
 *  "isBoolean" => true,
 * ));
 */
class HTMLCheckBox extends HTMLInput {
	var $label;
	var $elementId;
	var $selected;
	var $type = "checkbox";
	var $isBoolean;
	function setLabel($label){
		$this->label = $label;
	}
	function setSelected($selected){
		$this->selected = $selected;
	}
	function setElementId($elementId){
		$this->elementId = $elementId;
	}
	function getStartTag(){
		$zero = "";
		$label = '<?php if(strlen($'.$this->getPageParam().'["'.$this->getId().'"])>0){ ?><label for="<?php echo $'.$this->getPageParam().'["'.$this->getId().'_attribute"]["id"]; ?>">'.
			'<?php echo $'.$this->getPageParam().'["'.$this->getId().'"]; ?></label><?php } ?>';
		if($this->isBoolean()){
			$zero = '<input type="hidden" name="<?php echo $'.$this->getPageParam().'["'.$this->getId().'_attribute"]["name"]; ?>" value="0" />';
		}
		return $zero . parent::getStartTag() . $label;
	}
	function execute(){
		parent::execute();
		if(!$this->elementId) $this->elementId = "label_" . md5((string)$this->value.(string)$this->name.(string)rand(0,1));
		$this->setAttribute("id",$this->elementId);
		$checked = ($this->selected) ? "checked" : null;
		$this->setAttribute("checked",$checked, false);
	}
	function getLabel(){
		return (string) $this->label;
	}
	function getObject(){
		return htmlspecialchars($this->getLabel(),ENT_QUOTES,SOY2HTML::ENCODING);
	}
	function setIsBoolean($flag){
		$this->isBoolean = $flag;
	}
	function isBoolean(){
		return (boolean)$this->isBoolean;
	}
}
/* SOY2HTML/SOY2HTMLComponents/HTMLHead.class.php */
/**
 * @package SOY2.SOY2HTML
 * HTMLHeadコンポーネント
 *
 * title - タイトルを設定
 * isEraseHead - テンプレートにあるヘッドを削除するかどうか
 */
class HTMLHead extends SOY2HTML{
	var $tag = "head";
	var $title;
	var $isEraseHead = false;
	const SOY_TYPE = SOY2HTML::HTML_BODY;
	const HEAD_SCRIPT = "_script_";
	const HEAD_LINK = "_link_";
	const HEAD_META = "_meta_";
	function setTitle($title){
		$this->title = $title;
	}
	function getTitle(){
		return htmlspecialchars((string)$this->title,ENT_QUOTES,SOY2HTML::ENCODING);
	}
	function setIsEraseHead($boolean){
		$this->isEraseHead = (boolean)$boolean;
	}
	function getIsEraseHead(){
		return $this->isEraseHead;
	}
	protected static function &getHeads(){
		static $_array;
		if(!$_array){
			$_array = array(
				self::HEAD_SCRIPT => array(),
				self::HEAD_LINK => array(),
				self::HEAD_META => array()
			);
		}
		return $_array;
	}
	public static function addMeta($key,$array){
		$heads = &HTMLHead::getHeads();
		$heads[self::HEAD_META][$key] = $array;
	}
	public static function clearMeta($key){
		$heads = &HTMLHead::getHeads();
		$heads[self::HEAD_META][$key] = null;
		unset($heads[self::HEAD_META][$key]);
	}
	public static function addLink($key,$array){
		$heads = &HTMLHead::getHeads();
		$heads[self::HEAD_LINK][$key] = $array;
	}
	public static function clearLink($key){
		$heads = &HTMLHead::getHeads();
		$heads[self::HEAD_LINK][$key] = null;
		unset($heads[self::HEAD_LINK][$key]);
	}
	public static function addScript($key,$array){
		$heads = &HTMLHead::getHeads();
		$heads[self::HEAD_SCRIPT][$key] = $array;
	}
	public static function clearScript($key){
		$heads = &HTMLHead::getHeads();
		$heads[self::HEAD_SCRIPT][$key] = null;
		unset($heads[self::HEAD_SCRIPT][$key]);
	}
	function execute(){
		if($this->getIsModified() != true){
			return;
		}
		if($this->isEraseHead){
			$this->setInnerHTML("");
		}
		$innerHTML = $this->getInnerHTML();
		$innerHTML .= '<?php echo $'.$this->getPageParam().'["'.$this->getId().'"]["metas"]; ?>';
		$innerHTML .= '<?php echo $'.$this->getPageParam().'["'.$this->getId().'"]["links"]; ?>';
		$innerHTML .= '<?php echo $'.$this->getPageParam().'["'.$this->getId().'"]["scripts"]; ?>';
		$innerHTML .= "\n";
		if(preg_match('/<\/title>/i',$innerHTML)){
			$innerHTML = preg_replace('/<\/title>/i','<?php echo $'.$this->getPageParam().'["'.$this->getId().'"]["title"]; ?></title>',$innerHTML);
		}else{
			$innerHTML .= '<title><?php echo $'.$this->getPageParam().'["'.$this->getId().'"]["title"]; ?></title>'."\n";
		}
		$this->setInnerHTML($innerHTML);
	}
	function getObject(){
		return array(
			"title"   => $this->getTitle(),
			"metas"   => HTMLHead::getMetaHTML(),
			"links" => HTMLHead::getLinkHTML(),
			"scripts"   => HTMLHead::getScriptHTML(),
		);
	}
	function getMetaHTML(){
		$array = HTMLHead::getHeads();
		$metaArray = array();
		$metas = $array[self::HEAD_META];
		foreach($metas as $akey => $avalue){
			$attributes = array();
			foreach($avalue as $key => $value){
				$attributes[$key] = $key.'="'.htmlspecialchars((string)$value,ENT_QUOTES,SOY2HTML::ENCODING).'"';
			}
			$metaArray[$akey] = '<meta '.implode(" ",$attributes).'/>';
		}
		return  ((!empty($metaArray)) ? "\n" : "") . implode("\n",$metaArray);
	}
    function getScriptHTML(){
    	$array = HTMLHead::getHeads();
		$scriptArray = array();
		$scripts = $array[self::HEAD_SCRIPT];
		foreach($scripts as $akey => $avalue){
			$attributes = array();
			$body = "";
			foreach($avalue as $key => $value){
				$key = strtolower($key);
				if($key == "script"){
					$body = "<!--\n".$value."\n-->";//scriptの中身はHTML4ではCDATA, XHTML1だとPCDATA
					continue;
				}
				if($key == "src"){
				}
				$attributes[$key] = $key.'="'.htmlspecialchars((string)$value,ENT_QUOTES,SOY2HTML::ENCODING).'"';
			}
			if(!array_key_exists("type", $attributes)){
				$attributes["type"] = 'type="text/JavaScript"';
			}
			if(!array_key_exists("charset", $attributes)){
				$attributes["charset"] = 'charset="utf-8"';
			}
			$scriptArray[$akey] = '<script '.implode(" ",$attributes).'>'.( (strlen($body) >0) ? "\n".$body."\n" : "" ).'</script>';
		}
		return ((!empty($scriptArray)) ? "\n" : "") . implode("\n",$scriptArray);
    }
    function getLinkHTML(){
    	$array = HTMLHead::getHeads();
		$linkArray = array();
		$links = $array[self::HEAD_LINK];
		foreach($links as $akey => $avalue){
			$attributes = array();
			foreach($avalue as $key => $value){
				$attributes[$key] = $key.'="'.htmlspecialchars((string)$value,ENT_QUOTES,SOY2HTML::ENCODING).'"';
			}
			$linkArray[$akey] = '<link '.implode(" ",$attributes).'/>';
		}
		return ((!empty($linkArray)) ? "\n" : "") . implode("\n",$linkArray);
    }
}
/* SOY2HTML/SOY2HTMLComponents/HTMLImage.class.php */
/**
 * @package SOY2.SOY2HTML
 */
class HTMLImage extends SOY2HTML{
    var $src;
    const SOY_TYPE = SOY2HTML::SKIP_BODY;
    function setSrc($path){
    	$this->src = $path;
    }
    function setImagePath($path){
    	$this->setSrc($path);
    }
    function execute(){
    	$this->setAttribute("src",$this->src);
    }
    function getObject(){
    	return $this->src;
    }
    function setAlt($alt){
    	$this->setAttribute("alt",$alt);
    }
}
/* SOY2HTML/SOY2HTMLComponents/HTMLLabel.class.php */
/**
 * @package SOY2.SOY2HTML
 */
class HTMLLabel extends SOY2HTML{
	const SOY_TYPE = SOY2HTML::HTML_BODY;
	var $text;
	private $width;
	private $isFolding;
	private $foldingTag = "<br />";
	private $isHtml = false;
	private $suffix = "...";
	function setText($text){
		$this->text = (string)$text;
	}
	function getText(){
		return (string)$this->text;
	}
	function setHtml($html){
		$this->text = (is_string($html)) ? (string)$html : "";
		$this->isHtml = true;
	}
	function getObject(){
		$text = $this->getText();
		if($this->isHtml){
			return $text;
		}else{
			if(strlen($this->width) >0){
				if($this->isFolding != true){
					$width = max(0, $this->width - mb_strwidth($this->suffix));
					$short_text = mb_strimwidth($text,0,$width);
		    		if(mb_strwidth($short_text) < mb_strwidth($text)){
				    	$short_text .= $this->suffix;
		    		}
		    		if(mb_strwidth($short_text) < mb_strwidth($text)){
		    			$text = $short_text;
		    		}
					return htmlspecialchars($text,ENT_QUOTES,SOY2HTML::ENCODING);
				}else{
					$folded = "";
					while(strlen($text)>0){
						$tmp = mb_strimwidth($text, 0, $this->width);
						$text = mb_substr($text, mb_strlen($tmp));
						$folded .= htmlspecialchars($tmp,ENT_QUOTES,SOY2HTML::ENCODING);
						if(strlen($text) >0) $folded .= $this->foldingTag;
					}
					return $folded;
				}
			}else{
				return htmlspecialchars($text,ENT_QUOTES,SOY2HTML::ENCODING);
			}
		}
	}
	function setWidth($width){
		$this->width = $width;
	}
	function setIsFolding($flag){
		$this->isFolding = (boolean)$flag;
	}
	function setFoldingTag($tag){
		$this->foldingTag = $tag;
	}
	public function getSuffix() {
		return $this->suffix;
	}
	public function setSuffix($suffix) {
		$this->suffix = $suffix;
	}
}
/* SOY2HTML/SOY2HTMLComponents/HTMLLink.class.php */
/**
 * @package SOY2.SOY2HTML
 */
class HTMLLink extends HTMLLabel{
	var $tag = "a";
	const SOY_TYPE = SOY2HTML::HTML_BODY;
	var $link;
	var $target;
		function getStartTag(){
		return '<?php if(strlen($'.$this->getPageParam().'["'.$this->getId().'_attribute"]["href"])>0){ ?>' .
			parent::getStartTag() .
			'<?php } ?>';
	}
	function getEndTag(){
		return '<?php if(strlen($'.$this->getPageParam().'["'.$this->getId().'_attribute"]["href"])>0){ ?>' .
			parent::getEndTag() .
			'<?php } ?>';
	}
	function setLink($link){
		$this->link = $link;
	}
	function setTarget($target){
		$this->target = $target;
	}
	function execute(){
		if(!is_null($this->text)){
			parent::execute();
		}
		$suffix = $this->getAttribute($this->_soy2_prefix . ":suffix");
		if($suffix){
			$this->link .= $suffix;
		}
		$this->setAttribute("href",$this->link);
		if(strlen($this->target)){
			$this->setAttribute("target",$this->target);
		}elseif(isset($this->target)){
			$this->clearAttribute("target");
		}
	}
	function getObject(){
		if(!is_null($this->text)){
			return parent::getObject();
		}
		return $this->link;
	}
}
/**
 * @package SOY2.SOY2HTML
 *
 * @see function soy2_get_token
 */
class HTMLActionLink extends HTMLLink{
	function execute(){
		if(!is_null($this->text)){
			HTMLLabel::execute();
		}
		$link = $this->link;
		if(strpos($link,"?")===false){
			$link .= "?";
		}else{
			$link .= "&";
		}
		$link .= "soy2_token=" . soy2_get_token();
		$this->setAttribute("href",$link);
	}
}
/* SOY2HTML/SOY2HTMLComponents/HTMLList.class.php */
/**
 * @package SOY2.SOY2HTML
 */
class HTMLList extends SOYBodyComponentBase{
	var $list = array();
	var $_list = array();
	var $htmls = array();
	var $_includeParentTag = true;
	protected $_notMerge = false;
	function setList($list){
		if(!is_array($list)){
			$list = (array)$list;
		}
		$this->list = $list;
	}
	function getStartTag(){
		$this->_includeParentTag = $this->getAttribute("includeParentTag");
		$this->clearAttribute("includeParentTag");
		if($this->_includeParentTag){
		 	return SOY2HTML::getStartTag() . "\n".'<?php $'.$this->getId().'_counter = -1; foreach($'.$this->getPageParam().'["'.$this->getId().'"] as $key => $'.$this->getId().'){ $'.$this->getId().'_counter++; ?>';
		}else{
		 return '<?php $'.$this->getId().'_counter = -1;foreach($'.$this->getPageParam().'["'.$this->getId().'"] as $key => $'.$this->getId().'){ $'.$this->getId().'_counter++; ?>'
			 	. SOY2HTML::getStartTag();
		}
	}
	function getEndTag(){
		if($this->_includeParentTag){
		 	return '<?php } ?>' . "\n" .SOY2HTML::getEndTag();
		 }else{
			return SOY2HTML::getEndTag() . '<?php } ?>';
		}
	}
	function getObject(){
		return $this->_list;
	}
	function execute(){
		$innerHTML = $this->getInnerHTML();
		$old = error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
		$this->populateItemImpl(new HTMLList_DummyObject(),null,-1,count($this->list));
		$this->createAdd("index","HTMLLabel",array("text" => ""));
		$this->createAdd("loop","HTMLList_LoopModel",array("counter" => -1));
		$this->createAdd("at_first","HTMLModel",array("visible" => false));
		$this->createAdd("not_first","HTMLModel",array("visible" => false));
		$this->createAdd("at_last","HTMLModel",array("visible" => false));
		$this->createAdd("not_last","HTMLModel",array("visible" => false));
		error_reporting($old);
		parent::execute();
		$counter = 0;
		$length = count($this->list);
		foreach($this->list as $listKey => $listObj){
			$counter++;
			$tmpList = array();
			$res = $this->populateItemImpl($listObj,$listKey,$counter,$length);
			$this->createAdd("index","HTMLLabel",array("text" => $counter));
			$this->createAdd("loop","HTMLList_LoopModel",array("counter" => $counter));
			$this->createAdd("at_first","HTMLModel",array("visible" => $counter == 1));
			$this->createAdd("not_first","HTMLModel",array("visible" => $counter != 1));
			$this->createAdd("at_last","HTMLModel",array("visible" => $counter == $length));
			$this->createAdd("not_last","HTMLModel",array("visible" => $counter != $length));
			if($res === false)continue;
			foreach($this->_components as $key => $obj){
				$obj->setContent($innerHTML);
				$obj->execute();
				$this->set($key,$obj,$tmpList);
			}
			$this->_list[$listKey] = $tmpList;//WebPage::getPage($this->getParentId());
		}
	}
	function isMerge(){
		return false;
	}
	function populateItemImpl($entity,$key,$counter,$length){
		if(method_exists($this,"populateItem")){
			return $this->populateItem($entity,$key,$counter,$length);
		}
		if($this->_soy2_functions["populateItem"]){
			return $this->__call("populateItem",array($entity,$key,$counter,$length));
		}
		return null;
	}
}
/**
 * @package SOY2.SOY2HTML
 */
class HTMLList_DummyObject extends ArrayObject{
	function __call($func,$args){
		return new HTMLList_DummyObject();
	}
	function __get($key){
		return new HTMLList_DummyObject();
	}
	function __toString(){
		return "";
	}
}
/**
 * @package SOY2.SOY2HTML
 */
class HTMLList_LoopModel extends HTMLModel{
	private $counter;
	function getStartTag(){
		$step = (int)$this->getAttribute("step");
		$func ='<?php $'.$this->getId().'_loop_visible = !(boolean)'.$step.';' .
				'if('.$step.')$'.$this->getId().'_loop_visible=(($'.$this->getPageParam().'_counter+1) % '.$step.' === 0); ' .
		 		'if($'.$this->getId().'_loop_visible){ ?>';
		$res = $func . parent::getStartTag();
		return $res;
	}
	function getEndTag(){
		return parent::getEndTag() . "<?php } ?>";
	}
	function setCounter($counter){
		$this->counter = $counter;
	}
	function getCounter(){
		return $this->counter;
	}
}
/* SOY2HTML/SOY2HTMLComponents/HTMLPage.class.php */
/**
 * 各ページの設定をするクラスの基底となるクラス
 *
 * @package SOY2.SOY2HTML
 * @author Miyazawa
 */
class HTMLPage extends SOYBodyComponentBase{
	protected $_soy2_content;
	protected $_soy2_page;
	private $_soy2_body_element;
	private $_soy2_head_element;

	/**
	 * キャッシュファイルの生成に失敗しているか判定する為の文字数
	 * キャッシュファイルの生成に失敗すると白紙ページになってしまい、一定期間キャッシュが残ってしまう
	 */
	const CACHE_CONTENTS_LENGTH_MIN = 81;

	function __construct(){
		$this->prepare();
	}
	/**
	 * コンポーネントとして動作時
	 * 派生元のSOY2HTMLのsetIdメソッドのオーバーライド
	 *
	 * @see SOY2HTML.setId
	 */
	function setId($id){
		SOY2HTML::setId($id);
		$this->setPageParam($id);
	}
	/*
	function setParentPageParam($param){
		SOY2HTML::setParentPageParam($param);
	}
	*/
	/**
	 * コンポーネントとして動作時
	 * 派生元のSOY2HTMLのsetContentメソッドのオーバーライド
	 *
	 * @see SOY2HTML.setConetnt
	 */
	function setContent($content){
		parent::setContent($content);
		$this->setInnerHTML('<?php echo $'.$this->getParentPageParam().'["'.$this->getId().'"]; ?>');
	}
	/**
	 * コンストラクタより呼ばれ、Initializeを行う
	 */
	function prepare(){
		$this->init();
		$this->_soy2_page = array();
		$content = $this->getTemplate();
		if($content !== false && strlen($content)){
			/*
			 * PHPを許可しないときは<?と?>をエスケープする
			 * ただしXML宣言は残す
			 */
			if(defined("SOY2HTML_ALLOW_PHP_SCRIPT") && SOY2HTML_ALLOW_PHP_SCRIPT == false){
				$content = preg_replace('/\A<\?xml([^\?]*)\?>/sm','@@XML_START@@$1@@XML_END@@',$content);
				$content = str_replace(array('<?', '?>'), array('&lt;?', '?&gt;'), $content);
				$content = str_replace(array('@@XML_START@@', '@@XML_END@@'), array('<?xml', '?>'), $content);
			}
			/*
			 * PHPの短縮タグ（<?）が有効なときはxml宣言をechoするようにする
			 */
			if(ini_get("short_open_tag")){
				$content = preg_replace('/\A<\?xml/','<?php echo "<?xml"; ?>',$content);
			}
		}
		$this->_soy2_content = $content;
		if($this->_soy2_content === false && is_readable($this->getCacheFilePath(".inc.php"))){
			ob_start();
			include($this->getCacheFilePath(".inc.php"));
			$tmp = ob_get_contents();
			ob_end_clean();
			$this->_soy2_permanent_attributes = @unserialize($tmp);
		}
	}
	function getBodyElement(){
		if(is_null($this->_soy2_body_element))$this->_soy2_body_element = new HTMLPage_ChildElement("body");
		return $this->_soy2_body_element;
	}
	function getHeadElement(){
		if(is_null($this->_soy2_head_element))$this->_soy2_head_element = new HTMLPage_HeadElement("head");
		return $this->_soy2_head_element;
	}
	/**
	 * SOY2HTMLオブジェクトのインスタンスを作成
	 *
	 * @return SOY2HTML
	 * @param SoyId
	 * @param クラス名
	 * @param 初期値
	 */
	function create($id,$className,$array = array()){
		if(is_object($className)){
			$obj = $className;
			$obj->setId($id);
		}else{
			$obj = SOY2HTMLFactory::createInstance($className,$array);
			$obj->setId($id);
			$obj->setParentId($this->getId());
		}
		$obj->setParentObject($this);
		$obj->init();
		if($this->_soy2_content != false){
			$obj->setContent($this->_soy2_content);
		}else{
			$obj->setIsModified(false);
			if(isset($this->_soy2_permanent_attributes[$id])){
				foreach($this->_soy2_permanent_attributes[$id] as $key => $value){
					$obj->_soy2_attribute[$key] = $value;
				}
			}
		}
		if($obj instanceof HTMLPage){
			$obj->setParentPageParam($this->getPageParam());
			$obj->setPageParam($this->getPageParam());
		}else{
			$obj->setPageParam($this->getPageParam());
		}
		return $obj;
	}
	/**
	 * IDにたいしてオブジェクトを関連付け登録します
	 *
	 * @param $id ID名
	 * @param $obj SOY2HTMLより派生したクラスオブジェクト
	 *
	 * ex:
	 * 	<p soy:id="blog_title">タイトル</p>
	 *
	 * に対して
	 *
	 * 	$this->add("blog_title",SOY2HTMLFactory::createInstance("HTMLLabel",array(
	 * 		"text" => BLOG_TITLE,
	 * 		"tag" => "p"
	 * 	)));
	 *
	 */
	function add($id,$obj){
		if(!$obj instanceof SOY2HTML){
			return;
		}
		if($obj->getId() !== $id){
			$obj = $this->create($id,$obj);
		}
		$obj->execute();
		$this->set($id,$obj);
		if($this->_soy2_content != false){
			$this->_soy2_content = $this->getContent($obj,$this->_soy2_content);
			$this->_soy2_permanent_attributes[$id] = $obj->getPermanentAttribute();//
		}
	}
	/**
	 * コンポーネントクラスを指定してadd
	 *
	 * createしてからaddしてます。
	 *
	 * @param $id SoyId
	 * @param $className クラス名
	 * @param $array = array()　setter injection
	 * @see HTMLPage.add
	 */
	function createAdd($id,$className,$array = array()){
		$this->add($id,$this->create($id,$className,$array));
	}
	/**
	 * プラグインの実行
	 */
	function parsePlugin(){
		$plugin = new PluginBase();
		while(true && SOY2HTMLPlugin::length()){
			list($tag,$line,$innerHTML,$outerHTML,$value,$suffix,$skipendtag) = $plugin->parse("[a-zA-Z0-9]*","[a-zA-Z0-9\.\/\-_\?\&\=#]*",$this->_soy2_content);
			if(!strlen($tag))break;
			$tmpPlugin = $plugin->getPlugin($suffix);
			$plugin->_attribute = array();
			if(is_null($tmpPlugin)){
				$tmpTag = $plugin->getTag();
				$plugin->setTag($tag);
				$plugin->parseAttributes($line);
				$plugin->setInnerHTML($innerHTML);
				$plugin->setOuterHTML($outerHTML);
				$plugin->setSkipEndTag($skipendtag);
				$this->_soy2_content = $this->getContent($plugin,$this->_soy2_content);
				$this->_soy2_content = str_replace(":".$suffix,"",$this->_soy2_content);
				$plugin->setTag($tmpTag);
				continue;
			}
			$tmpPlugin->_attribute = array();
			$tmpPlugin->setTag($tag);
			$tmpPlugin->parseAttributes($line);
			$tmpPlugin->setInnerHTML($innerHTML);
			$tmpPlugin->setOuterHTML($outerHTML);
			$tmpPlugin->setParent($this);
			$tmpPlugin->setSkipEndTag($skipendtag);
			$tmpPlugin->setSoyValue($value);
			$tmpPlugin->execute();
			$this->_soy2_content = $this->getContent($tmpPlugin,$this->_soy2_content);
		}
		$plugin = null;
	}
	/**
	 * キャッシュを生成し、画面上にContentを表示します
	 */
	function display(){
		if($this->_soy2_body_element)$this->_soy2_content = $this->_soy2_body_element->convert($this->_soy2_content,$this->getPageParam());
		if($this->_soy2_head_element)$this->_soy2_content = $this->_soy2_head_element->convert($this->_soy2_content,$this->getPageParam());
		$page = &$this->_soy2_page;
		if($this->_soy2_body_element)$page = $this->_soy2_body_element->execute($page);
		if($this->_soy2_head_element)$page = $this->_soy2_head_element->execute($page);
		$this->parsePlugin();
		$this->parseMessageProperty();
		$filePath = $this->getCacheFilePath();
		$this->createCacheFile();
		$this->createPermanentAttributesCache();
		if(file_exists($filePath)){	//キャッシュファイルを作成できなかった場合
			$page = &HTMLPage::getPage();
			if($this->getId()){
				$page[$this->getId()] = $this->_soy2_page;
			}else{
				$page = $this->_soy2_page;
			}
			ob_start();
			include($filePath);
			$html = ob_get_contents();
			ob_end_clean();
		}else{
			$html = "";
		}

		$layoutDir = SOY2HTMLConfig::LayoutDir();
		$layout = $this->getLayout();
		if($layoutDir && is_file($layoutDir . $layout)){
			include($layoutDir . $layout);
		}else{
			echo $html;
		}
		self::popPageStack();
	}
	/**
	 * 対応する部分の書き換え
	 * @see SOY2HTML.execute
	 */
	function execute(){
		$this->_soy2_innerHTML = '<?php echo @$'.$this->getParentPageParam().'["'.$this->getId().'"]; ?>';
	}
	/**
	 * 現在のContentを取得します
	 *
	 * @return 現在のContent
	 */
	function getObject(){
		ob_start();
		$this->display();
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
	/**
	 * テンプレートファイルの読み込み
	 *
	 * 上書きすることで独自のテンプレートを使用可能
	 *
	 * @return 読み込まれたTemplete（Content)
	 */
	function getTemplate(){
		if($this->isModified() != true){
			return false;
		}
		$file = $this->getTemplateFilePath();
		if(!file_exists($file)){
			return "";
		}
		return file_get_contents($file);
	}
	/**
	 * テンプレートファイルパスの読み込み
	 * これを上書きすることで任意のテンプレートを読み込ませることが出来る
	 * @return テンプレートファイルのパス
	 */
	function getTemplateFilePath(){
		$dir = dirname($this->getClassPath());
		if(strlen($dir)>0 && $dir[strlen($dir)-1] != "/")$dir .= "/";	//end by "/"
		$templateDir = SOY2HTMLConfig::TemplateDir();
		if($templateDir){
			$pageDir = SOY2HTMLConfig::PageDir();
			$dir = str_replace($pageDir,$templateDir,$dir);
		}
		$lang = SOY2HTMLConfig::Language();
		if(strlen($lang) > 0){
			$lang_html = $dir . get_class($this) . "_" . $lang . ".html";
			if(file_exists($lang_html)){
				return $lang_html;
			}
		}
		//隠しモード：同名のHTMLファイルのファイル名の頭に_(アンダースコア)を付与すると優先的に読み込む
		$hidden_mode_html = $dir . "_" . get_class($this) . ".html";
		if(file_exists($hidden_mode_html)){
			return $hidden_mode_html;
		}

		return $dir . get_class($this) . ".html";
	}
	/**
	 * キャッシュファイルのパス
	 *
	 * @return キャッシュファイルのパス
	 */
	function getCacheFilePath($extension = ".html.php"){
		return
			SOY2HTMLConfig::CacheDir()
			.SOY2HTMLConfig::getOption("cache_prefix") .
			"cache_" . get_class($this) .'_'. $this->getId() .'_'. $this->getParentPageParam()
			."_". md5($this->getClassPath().$this->getTemplateFilePath())
			."_".SOY2HTMLConfig::Language()
			.$extension;
	}
	/**
	 * キャッシュファイルにファイルの書き込み
	 *
	 */
	function createCacheFile(){
		$filePath = $this->getCacheFilePath();
		if(!strlen($filePath)){
			return;
		}
		if(!$this->isModified()){
			return;
		}
		$fp = @fopen($filePath,"w");
		if(!$fp){
			throw new SOY2HTMLException("[SOY2HTML]Can not create cache file.");
		}
		fwrite($fp,'<?php /* created ' . date("Y-m-d h:i:s") .' */ ?>');
		fwrite($fp,"\r\n");
		if(strlen($this->getId())){
			fwrite($fp,'<?php $'.$this->getPageParam().' = HTMLPage::getPage("'.$this->getId().'"); ?>');
		}else{
			fwrite($fp,'<?php $'.$this->getPageParam().' = HTMLPage::getPage(); ?>');
		}
		fwrite($fp,"\r\n");
		fwrite($fp,$this->_soy2_content);
		fclose($fp);
	}
	/**
	 * 永続化の属性値を作成
	 */
	function createPermanentAttributesCache(){
		$filePath = $this->getCacheFilePath(".inc.php");
		if($this->isModified() != true && file_exists($filePath)){
			return;
		}
		$fp = @fopen($filePath,"w");
		fwrite($fp,"<?php ");
		fwrite($fp,'echo \''.serialize($this->_soy2_permanent_attributes).'\';');
		fwrite($fp,"?>");
		fclose($fp);
	}
	/**
	 * キャッシュを作成するかどうか
	 * @return true:作成すべき false:しなくてもよい
	 */
	function isModified(){
		$filePath = $this->getCacheFilePath();
		//キャッシュの出力に失敗した場合は強制的にキャッシュの生成 キャッシュの生成に失敗した時、キャッシュファイルの文字数が81になるので、81以下の場合は失敗と見なす
		if(file_exists($filePath)){
			$len = 0;
			$fp = fopen($filePath, "r");
			if($fp){
				while ($line = fgets($fp)) {
					$len += strlen(trim($line));
					if($len > self::CACHE_CONTENTS_LENGTH_MIN) break;
  				}
			}
			fclose($fp);
			if($len <= self::CACHE_CONTENTS_LENGTH_MIN) return true;
		}
		$templateFilePath = $this->getTemplateFilePath();
		$reflection = new ReflectionClass(get_class($this));
		$classFilePath = $reflection->getFileName();
		if(defined("SOY2HTML_CACHE_FORCE") && SOY2HTML_CACHE_FORCE == true){
			return true;
		}
		if(!file_exists($templateFilePath)){
			return false;
		}
		if(
			file_exists($filePath)
			&& filemtime(__FILE__) <= filemtime($filePath)
			&& filemtime($templateFilePath) <= filemtime($filePath)
			&& filemtime($classFilePath) <= filemtime($filePath)
		){
			return false;
		}
		return true;
	}
	/**
	 * ページを取得する
	 *
	 * @return ページ
	 */
	public static function &getPage($id = null){
		static $page;
		if(is_null($page)){
			$page = array();
		}
		$tmpPage = &$page;
		$pageStack = self::$_soy2_page_stack;
		foreach($pageStack as $stack){
			if(!isset($tmpPage[$stack]))$tmpPage[$stack] = array();
			$tmpPage = &$tmpPage[$stack];
		}
		if($id){
			if(!isset($tmpPage[$id]))$tmpPage[$id] = array();
			return $tmpPage[$id];
		}
		return $tmpPage;
	}
	/*
	 * 以下、ページの入れ子構造を実現するためのスタック
	 */
	private static $_soy2_page_stack = array();
	private static function pushPageStack($id){
		if(!$id)return;
		self::$_soy2_page_stack[] = $id;
	}
	private static function popPageStack(){
		array_pop(self::$_soy2_page_stack);
	}
	/**
	 * ページに値をセットする
	 * @see SOY2HTML.set
	 */
	function set($id,SOY2HTML &$obj,&$page = null){
		$page = &$this->_soy2_page;
		parent::set($id,$obj,$page);
	}
	/**
	 * タイトルを書き換える
	 */
	function setTitle($title){
		$this->getHeadElement()->setTitle($title);
	}
	/**
	 * @override
	 * MessagePropertyを置き換える
	 */
	function parseMessageProperty(){
		if($this->getIsModified()){
			foreach($this->_message_properties as $key => $message){
				$tmpKey = "@@".$key.";";
				$this->_soy2_content = str_replace($tmpKey,$message,$this->_soy2_content);
			}
		}
	}
	/**
	 * レイアウトを取得
	 * HTMLPageのディフォルトはnull(レイアウトを使わない)
	 */
	function getLayout(){
		return null;
	}
}
/**
 * HTMLTemplatePageではテンプレートHTMLをパラメータとして渡す
 * テンプレートファイルがないのでその点でキャッシュ周りの処理が変わる
 *
 * 例
 * $htmlObj->create("some_soy_id","HTMLTemplatePage", array(
 * 	"arguments" => array("some_soy_id","<h1>test</h1><p soy:id=\"test\">test</p>")
 * ));
 */
class HTMLTemplatePage extends HTMLPage{
	var $_id;
	var $_html;
	private $hash = "";
	function __construct($args){
		$this->_id = $args[0];
		$this->_html = $args[1];
		$this->hash = md5($this->_html);
		parent::__construct();
	}
	function getTemplate(){
		return $this->_html;
	}
	function getId(){
		return $this->_id;
	}
	function getParentId(){
		return $this->getId();
	}
	function getPageParam(){
		return $this->_id;
	}
	function getCacheFilePath($extension = ".html.php"){
		return SOY2HTMLConfig::CacheDir()
			.SOY2HTMLConfig::getOption("cache_prefix") .
			"cache_" . 'template' .'_'. $this->getId() .'_'. $this->getParentPageParam()
			."_". $this->hash
			."_".SOY2HTMLConfig::Language()
			.$extension;
	}
	function isModified(){
		if(defined("SOY2HTML_CACHE_FORCE") && SOY2HTML_CACHE_FORCE == true){
			return true;
		}
		$filePath = $this->getCacheFilePath();
		if("HTMLTemplatePage" == ($class = get_class($this))){
			$classFilePath = __FILE__;
		}else{
			$reflection = new ReflectionClass(get_class($this));
			$classFilePath = $reflection->getFileName();
		}
		if(
			file_exists($filePath)
			&& filemtime(__FILE__) <= filemtime($filePath)
			&& filemtime($classFilePath) <= filemtime($filePath)
		){
			return false;
		}
		return true;
	}
}
/**
 * 子エレメント
 * 追記したりとか
 */
class HTMLPage_ChildElement{
	protected $tag;
	private $insert = array();
	private $append = array();
	function __construct($tag){
		$this->tag = $tag;
	}
	function insertHTML($html){
		$this->insert[] = $html;
	}
	function appendHTML($html){
		$this->append[] = $html;
	}
	function execute($array){
		$array["page_" . $this->tag . "_insert"] = implode("\n",$this->insert);
		$array["page_" . $this->tag . "_append"] = implode("\n",$this->append);
		return $array;
	}
	function convert($html,$pageParam){
		if($html != false){
			if(preg_match('/(<'.$this->tag.'\s?[^>]*>)/i',$html,$tmp1,PREG_OFFSET_CAPTURE)){
			 	$start = $tmp1[1][0];
			 	$out = $tmp1[1][0] . "\n" . '<?php echo $'.$pageParam.'["page_'.$this->tag.'_insert"]; ?>';
				$html = str_replace($start,$out,$html);
			}
			if(preg_match('/(<\/'.$this->tag.'\s?[^>]*>)/i',$html,$tmp1,PREG_OFFSET_CAPTURE)){
				$start = $tmp1[1][0];
			 	$out = '<?php echo $'.$pageParam.'["page_'.$this->tag.'_append"]; ?>' ."\n" . $tmp1[1][0];
				$html = str_replace($start,$out,$html);
			}
		}
		return $html;
	}
}
class HTMLPage_HeadElement extends HTMLPage_ChildElement{
	private $title;
	private $metas = array();
	function __construct($tag = null){
		if($tag == null)$tag = "head";
		parent::__construct($tag);
	}
	function setTitle($title){
		$this->title = $title;
	}
	function getTitle(){
		return $this->title;
	}
	function _getMeta($name){
		if(!isset($this->metas[$name])){
			$this->metas[$name] = array("insert"=>"","content"=>false,"append"=>"");	//
		}
		return $this->metas[$name];
	}
	/**
	 * 元のmetaに書かれているものは残して後ろに追加
	 */
	function appendMeta($name,$content){
		$array = $this->_getMeta($name);
		$array["append"] .= $content;
		$this->metas[$name] = $array;
	}
	/**
	 * 元のmetaに書かれているものをは残して前に追加
	 */
	function insertMeta($name,$content){
		$array = $this->_getMeta($name);
		$array["insert"] .= $content;
		$this->metas[$name] = $array;
	}
	/**
	 * 元のmetaに書かれているものを消去
	 */
	function setMeta($name,$content){
		$array = $this->_getMeta($name);
		$array["content"] = $content;
		$this->metas[$name] = $array;
	}
	/**
	 * 設定を一回空にする(setMeta falseでもいいかもしれない)
	 */
	function clearMeta($name){
		if(isset($this->metas[$name]))unset($this->metas[$name]);
	}
	function execute($array){
		$array["page_" . $this->tag . "_title"] = $this->getTitle();
		$array["page_" . $this->tag . "_meta"] = $this->metas;
		$array = parent::execute($array);
		return $array;
	}
	function convert($html,$pageParam){
		if($html != false){
			if( preg_match('/(<title\s?[^>]*>)/i',$html,$tmp1,PREG_OFFSET_CAPTURE)
			 && preg_match('/(<\/title\s?[^>]*>)/i',$html,$tmp2,PREG_OFFSET_CAPTURE)
			){
				$start = $tmp1[1][1];
				$end = $tmp2[1][1] + strlen($tmp2[1][0]);
				$out = $tmp1[1][0] . '<?php echo htmlspecialchars($'.$pageParam.'["page_'.$this->tag.'_title"],ENT_QUOTES); ?>' . $tmp2[1][0];
				$in = substr($html,$start,$end - $start);
				$html= str_replace($in,$out,$html);
			}
			preg_match_all('/(<meta([^>]*)\/?>)/i',$html,$meta,PREG_OFFSET_CAPTURE);
			$added = array();
			foreach($meta[1] as $key => $array){
				if(preg_match('/<?php/',$meta[2][$key][0])){
					continue;
				}
				if(preg_match('/name\s*=\s*"([^"]+)"/i',$meta[2][$key][0],$tmp)){
					$name = $tmp[1];
					$content = "";
					if(preg_match('/content\s*=\s*"([^"]+)"/i',$meta[2][$key][0],$tmp2)){
						$content = $tmp2[1];
					}
					$replace = '<?php ' .
					           '$content = "'.strtr($content,array("'" => "\\'", "\\" => "\\\\")).'"; ' .
					           'if(isset($'.$pageParam.'["page_'.$this->tag.'_meta"]["'.$name.'"])){ ' .
					           '  $array = $'.$pageParam.'["page_'.$this->tag.'_meta"]["'.$name.'"]; ' .
					           '  if($array["content"] == false){ $content = htmlspecialchars($array["insert"],ENT_QUOTES,SOY2HTML::ENCODING) . $content . htmlspecialchars($array["append"],ENT_QUOTES,SOY2HTML::ENCODING); }else{ $content = htmlspecialchars($array["content"],ENT_QUOTES,SOY2HTML::ENCODING); }' .
					           '}' .
					           'echo \'<meta name="'.htmlspecialchars($name,ENT_QUOTES,SOY2HTML::ENCODING).'" content="\'.$content.\'" />\' . "\n"; ?>';
					$html = str_replace($array[0],$replace,$html);
					$added[] = $name;
				}
			}
			$head = "";
			foreach($this->metas as $key => $array){
				if(in_array($key,$added))continue;
				$head = '<?php if(isset($'.$pageParam.'["page_'.$this->tag.'_meta"]["'.$key.'"])){ ' .
						'	echo \'<meta name="'.htmlspecialchars($key,ENT_QUOTES).'" content="\'.htmlspecialchars(' .
								'$'.$pageParam.'["page_'.$this->tag.'_meta"]["'.$key.'"]["insert"] . ' .
								'$'.$pageParam.'["page_'.$this->tag.'_meta"]["'.$key.'"]["content"] . ' .
								'$'.$pageParam.'["page_'.$this->tag.'_meta"]["'.$key.'"]["append"],ENT_QUOTES' .
							').\'" />\' . "\n";'.
						'} ?>';
			}
			if(strlen($head) >0 && stripos($html,'</head>')!==false){
	    		$html = preg_replace('/<\/head>/i',$head.'</head>',$html);
			}
		}
		$html = parent::convert($html,$pageParam);
		return $html;
	}
}
/* SOY2HTML/SOY2HTMLComponents/HTMLPager.class.php */
/**
 * ページャーコンポーネント
 */
class HTMLPager extends SOYBodyComponentBase{
	private $link;
	private $page = 1;
	private $start = 0;
	private $end = 0;
	private $total = 0;
	private $query = "";
	private $pagerCount = 10;
	private $limit = 0;
    function execute(){
    	if($this->_soy2_parent){
			$this->_soy2_parent->createAdd("count_start","HTMLLabel",array(
				"text" => $this->getStart()
			));
			$this->_soy2_parent->createAdd("count_end","HTMLLabel",array(
				"text" => $this->getEnd()
			));
			$this->_soy2_parent->createAdd("count_max","HTMLLabel",array(
				"text" => $this->getTotal()
			));
    	}
		$next = $this->getNextParam();
		$this->createAdd("next_link","HTMLLink",$next);
		$this->createAdd("next_link_wrap","HTMLModel",array("visible" => $next["visible"]));
		$prev = $this->getPrevParam();
		$this->createAdd("prev_link","HTMLLink",$prev);
		$this->createAdd("prev_link_wrap","HTMLModel",array("visible" => $prev["visible"]));
		$this->createAdd("pager_list","SOY2HTMLPager_List",$this->getPagerParam());
		$this->createAdd("pager_jump","HTMLForm",array(
			"method" => "get",
			"action" => $this->getLink()
		));
		$this->createAdd("pager_select","HTMLSelect",array(
			"name" => "page",
			"options" => $this->getSelectArray(),
			"selected" => $this->getPage(),
			"onchange" => "location.href=this.parentNode.action+this.options[this.selectedIndex].value"
		));
    	parent::execute();
    }
    function getNextParam(){
		$link = ($this->total > $this->end) ? $this->link . ($this->page + 1) : $this->link . $this->page;
		if(strlen($this->getQuery()))$link .= "?" . $this->getQuery();
		return array(
    		"link" => $link,
    		"class" => ($this->total <= $this->end) ? "pager_disable" : "",
    		"visible" => ($this->total > $this->end)
    	);
	}
	function getPrevParam(){
		$link = ($this->page > 1) ? $this->link . ($this->page - 1) : $this->link . ($this->page);
		if(strlen($this->getQuery()))$link .= "?" . $this->getQuery();
		return array(
    		"link" => $link,
    		"class" => ($this->page <= 1) ? "pager_disable" : "",
    		"visible" => ($this->page > 1)
    	);
	}
	function getPagerParam(){
    	if($this->pagerCount < 0){
    		$pagers = range(
    			1,$this->getLastPageNum()
    		);
    	}else{
    		if($this->getLastPageNum() <= $this->pagerCount){
	    		$pagers = range(1, $this->getLastPageNum());
    		}else{
	    		$pagers = range(
		    		max(1,                 min($this->page - floor($this->pagerCount/2), $this->getLastPageNum() - $this->pagerCount)),
		    		max($this->pagerCount, min($this->page + ceil($this->pagerCount/2) -1, $this->getLastPageNum()))
		    	);
    		}
    	}
		return array(
    		"url" => $this->link,
    		"current" => $this->page,
    		"list" => $pagers,
    		"visible" => ($this->getLastPageNum() > 1)
    	);
	}
	function getLastPageNum(){
		return ceil($this->total / $this->limit);
	}
	function getSelectArray(){
    	$pagers = range(
    		1,
    		(int)($this->total / $this->limit) + 1
    	);
		$array = array();
		foreach($pagers as $page){
			$array[ $page ] = $page;
		}
		return $array;
	}
    function getLink() {
    	return $this->link;
    }
    function setLink($link) {
    	$this->link = $link;
    }
    function getPage() {
    	return $this->page;
    }
    function setPage($page) {
    	$this->page = $page;
    }
    function getStart() {
    	return min($this->start,$this->total);
    }
    function setStart($start) {
    	$this->start = $start;
    }
    function getEnd() {
    	if(!$this->end){
    		$this->end = min($this->total,$this->start + $this->limit - 1);
    	}
    	return $this->end;
    }
    function setEnd($end) {
    	$this->end = $end;
    }
    function getTotal() {
    	return $this->total;
    }
    function setTotal($total) {
    	$this->total = $total;
    }
    function getQuery() {
    	return $this->query;
    }
    function setQuery($query) {
    	$this->query = $query;
    }
    function getPagerCount() {
    	return $this->pagerCount;
    }
    function setPagerCount($count) {
    	$this->pagerCount = $count;
    }
    function getLimit() {
    	return $this->limit;
    }
    function setLimit($limit) {
    	$this->limit = $limit;
    }
}
class SOY2HTMLPager_List extends HTMLList{
	private $url;
	private $current;
	protected function populateItem($bean){
		if(is_array($bean)){
			list($link,$text) = $bean;
		}else{
			$link = $bean;
			$text= $bean;
		}
		$url = $this->url . $link;
		$this->createAdd("page_link","HTMLLink",array(
			"text" => $text,
			"link" => ($this->current != $link)?$url : ""
		));
		$this->createAdd("page_link_only","HTMLLink",array(
			"link" => $url
		));
		$this->createAdd("page_text","HTMLLabel",array(
			"text" => $text
		));
		$this->createAdd("current_page","HTMLModel",array(
			"visible" => ($this->current == $link)
		));
		$this->createAdd("other_page","HTMLModel",array(
			"visible" => ($this->current != $link)
		));
	}
	function getUrl() {
		return $this->url;
	}
	function setUrl($url) {
		$this->url = $url;
	}
	function getCurrent() {
		return $this->current;
	}
	function setCurrent($cuttent) {
		$this->current = $cuttent;
	}
}
/* SOY2HTML/SOY2HTMLComponents/HTMLScript.class.php */
/**
 * @package SOY2.SOY2HTML
 */
class HTMLScript extends SOY2HTML{
    var $tag = "script";
    const SOY_TYPE = SOY2HTML::HTML_BODY;
    var $script = "";
    var $type = "text/javascript";
    function setScript($script){
    	$this->script = $script;
    }
    function setSrc($src){
    	$this->setAttribute("src",$src);
    }
    function execute(){
    	$this->setAttribute("type",$this->type);
    	parent::execute();
    }
    function setType($type){
    	$this->type = $type;
    }
    function getObject(){
    	if(strlen($this->script)){
    		return "<!--\n".$this->script."\n-->";//htmlspecialchars((string)$this->script,ENT_QUOTES,SOY2HTML::ENCODING)
    	}else{
    		return $this->script;
    	}
    }
}
/* SOY2HTML/SOY2HTMLComponents/HTMLTree.class.php */
class HTMLTreeComponent_Child extends HTMLModel{
	private $func = "";
	function getStartTag(){
		$tag = parent::getStartTag();
		return
			'<?php foreach($'.$this->getParentId().'_child as $'.$this->getParentId() . "_" . $this->getId().'_child){ ?>' .
			$tag . "";
	}
	function getEndTag(){
		$tag = parent::getEndTag();
		return $tag . "<?php } /* end of loop of ".$this->getId()."*/ ?>" . "\n";;
	}
	function execute(){
		$this->_soy2_innerHTML = "<?php ".$this->func.'($'.$this->getParentId() . "_" . $this->getId() . '_child); ?>';
	}
	function getFunc() {
		return $this->func;
	}
	function setFunc($func) {
		$this->func = $func;
	}
}
class HTMLTreeComponent_ChildWrap extends HTMLModel{
	function getStartTag(){
		$tag = parent::getStartTag();
		return
			'<?php if(count($'.$this->getParentId().'_child) > 0){ ?>' .
			$tag . "\n";
	}
	function getEndTag(){
		$tag = parent::getEndTag();
		return $tag . "<?php } /* end of ".$this->getId()."*/ ?>" . "\n";;
	}
}
class HTMLTree extends SOYBodyComponentBase{
	public $tree;
	public $list;
	public $_funcName;
	private $_list = array();
    function getStartTag(){
		$tag = parent::getStartTag();
		$tag .= "<?php function " . $this->getFuncName() . '($'.$this->getId().'){ /* echo "<pre style=text-align:left;>";print_r($'.$this->getId().');echo "</pre>" */;' .
				'$'.$this->getId().'_child = $'.$this->getId().'["child"];' .
				'$'.$this->getId().' = $'.$this->getId().'["object"];' .
				"?>";
		return $tag;
	}
	function getEndTag(){
		$tag = "<?php } /* end of func ".$this->getFuncName()." */ ?>";
		$tag .= parent::getEndTag();
		$tag .= '<?php foreach($'.$this->getPageParam().'["'.$this->getId().'"] as $'.$this->getId().'_key => $'.$this->getId().'){' .
					$this->getFuncName() . '($'.$this->getId(). '); ' .
			    '} ?>';
		return $tag;
	}
	function getObject(){
		return $this->_list;
	}
	function getFuncName(){
		if(!$this->_funcName){
			$this->_funcName = "_soy2html_tree_component_" . $this->getId() . "_" . time();
		}
		return $this->_funcName;
	}
	function execute(){
		$innerHTML = $this->getInnerHTML();
		$this->populateItemImpl(new HTMLList_DummyObject,-1,-1);
		$this->createAdd("tree","HTMLTreeComponent_Child",array("func" => $this->getFuncName()));
		$this->createAdd("tree_child","HTMLTreeComponent_ChildWrap");
		parent::execute();
		$this->_list = $this->parseTree($this->tree);
	}
	function parseTree($tree,$depth = 0){
		$innerHTML = $this->getInnerHTML();
		$list = array();
		$counter = 0;
		foreach($tree as $treeKey => $treeArray){
			$isLast = false;
			$counter++;
			if(!is_array($treeArray)){
				$isLast = (count($tree) == $counter);
				$treeKey = $treeArray;
				$treeArray = array();
			}else{
				$isLast = (count($treeArray) < 1);
			}
			if(!isset($this->list[$treeKey]))continue;
			$tmpList = array();
			$listObj = $this->list[$treeKey];
			$new_depth = $depth + 1;
			$res = $this->populateItemImpl($listObj,$treeKey,$new_depth,$isLast);
			if($res === false)continue;
			foreach($this->_components as $key => $obj){
				$obj->setContent($innerHTML);
				$obj->execute();
				$this->set($key,$obj,$tmpList);
			}
			$child = (is_array($treeArray)) ? $this->parseTree($treeArray,$new_depth) : array();
			$list[$treeKey] = array(
				"object" => $tmpList,
				"child" => $child
			);
		}
		return $list;
	}
	function populateItemImpl($entity,$key,$depth,$isLast = false){
		if(method_exists($this,"populateItem")){
			return $this->populateItem($entity,$key,$depth,$isLast);
		}
		if($this->_soy2_functions["populateItem"]){
			return $this->__call("populateItem",array($entity,$key,$depth,$isLast));
		}
		return null;
	}
    function getList() {
    	return $this->list;
    }
    function setList($list) {
    	$this->list = $list;
    }
    function getTree() {
    	return $this->tree;
    }
    function setTree($tree) {
    	$this->tree = $tree;
    }
    function setTreeIds($ids){
    	$list = array();
    	$tmp = null;
    	foreach($ids as $id){
    		if(!is_null($tmp)){
    			$tmp[$id] = array();
    			$tmp = &$tmp[$id];
    		}else{
    			$list[$id] = array();
    			$tmp = &$list[$id];
    		}
    	}
    	$this->setTree($list);
    }
}
/* SOY2HTML/SOY2HTMLComponents/PluginBase.class.php */
/**
 * @package SOY2.SOY2HTML
 */
class PluginBase extends SOY2HTML{
	const SOY_TYPE = SOY2HTML::HTML_BODY;
	protected $soyValue = "";
	protected $parent = null;
	function setSoyValue($value){
		$this->soyValue = $value;
	}
	function setParent($page){
		$this->parent = $page;
	}
	function execute(){
    	if($this->functionExists("executePlugin")){
    		$this->__call("executePlugin",array($this->soyValue));
    	}else{
    		$this->executePlugin($this->soyValue);
    	}
    }
    function getObject(){
    }
    function getPlugin($param){
    	$plugin = SOY2HTMLPlugin::getPlugin($param);
    	if(is_null($plugin))return $plugin;
    	if(is_object($plugin)){
    		return $plugin;
    	}
    	return new $plugin();
    }
    function executePlugin($soyValue){
    }
    function getVisbleScript(){
    	return array("","");
    }
}
/**
 * @package SOY2.SOY2HTML
 */
class SOY2HTMLPlugin{
	private static function &getPlugins(){
		static $_static;
		if(is_null($_static)){
			$_static = array();
		}
		return $_static;
	}
	public static function addPlugin($key,$value){
		$plugins = &SOY2HTMLPlugin::getPlugins();
		$plugins[$key] = $value;
	}
	public static function getPlugin($key){
		$plugins = SOY2HTMLPlugin::getPlugins();
		return (isset($plugins[$key])) ? $plugins[$key] : null;
	}
	public static function removePlugin($key){
		$plugins = &SOY2HTMLPlugin::getPlugins();
		@$plugins[$key] = null;
	}
	public static function length(){
		return count(SOY2HTMLPlugin::getPlugins());
	}
}
/**
 * @package SOY2.SOY2HTML
 */
class PagePlugin extends PluginBase{
	var $isOverWrite = false;
	function executePlugin($soyValue){
    	$innerHTML = array();
    	$innerHTML[] = '<?php if(!isset($'.$this->parent->getPageParam().'["page_'.md5($soyValue).'"])){ ?>';
    	$innerHTML[] = '<?php $'.$this->parent->getPageParam().'["page_'.md5($soyValue).'"] = PagePlugin::loadWebPage("'.$this->parent->getId().'","'.$this->parent->getClassPath().'","'.$soyValue.'",__FILE__); ?>';
    	$innerHTML[] = '<?php } ?>';
    	$innerHTML[] = '<?php echo $'.$this->parent->getPageParam().'["page_'.md5($soyValue).'"]; ?>';
    	$this->setInnerHTML(implode("\n",$innerHTML));
    }
    function getStartTag(){
    	if($this->getAttribute("isOverWrite")){
    		$this->isOverWrite = (boolean)$this->getAttribute("isOverWrite");
    		$this->clearAttribute("isOverWrite");
    	}
    	if($this->isOverWrite){
    		return "";
    	}
    	return parent::getStartTag();
    }
    function getEndTag(){
    	if($this->isOverWrite){
    		return "";
    	}
    	return parent::getEndTag();
    }
    public static function loadWebPage($parentId,$parentClassPath,$className,$parentFilePath){
    	$id = "page_".md5($className.$parentClassPath);
    	$class = SOY2HTMLFactory::pageExists($className);
    	$filePath = str_replace("\\","/",realpath(SOY2HTMLConfig::PageDir().str_replace(".","/",$className).".class.php"));
		$parentPageParam = "";
    	$cachFilePath = SOY2HTMLConfig::CacheDir().
			SOY2HTMLConfig::getOption("cache_prefix") .
			"cache_" . $class .'_'. $id .'_'. $parentPageParam
			."_". md5($filePath)
			."_".SOY2HTMLConfig::Language()
			.".html.php";
		if(file_exists($cachFilePath) && filemtime($cachFilePath) < filemtime($parentFilePath)){
			unlink($cachFilePath);
		}
    	$webPage = SOY2HTMLFactory::createInstance($className);
    	$webPage->setId($id);
    	$webPage->setPageParam($id);
    	$webPage->setParentId($parentId);
    	$webPage->setParentPageParam($parentPageParam);
    	$webPage->execute();
    	$value = $webPage->getObject();
    	return $value;
    }
}
/**
 * @package SOY2.SOY2HTML
 */
class LinkPlugin extends PluginBase{
	function executePlugin($soyValue){
		if(strpos($soyValue,"/") !== false){
			$this->_attribute["href"] = SOY2PageController::createRelativeLink($soyValue);
		}else{
			$this->_attribute["href"] = SOY2PageController::createLink($soyValue);
		}
	}
}
/**
 * @package SOY2.SOY2HTML
 */
class SrcPlugin extends PluginBase{
	function executePlugin($soyValue){
		if(strpos($soyValue,"/") !== false){
			$this->_attribute["src"] = SOY2PageController::createRelativeLink($soyValue);
		}else{
			$this->_attribute["src"] = SOY2PageController::createLink($soyValue);
		}
	}
}
/**
 * @package SOY2.SOY2HTML
 */
class ActionPlugin extends PluginBase{
	function executePlugin($soyValue){
		if(strpos($soyValue,"/") !== false){
			$this->_attribute["action"] = SOY2PageController::createRelativeLink($soyValue);
		}else{
			$this->_attribute["action"] = SOY2PageController::createLink($soyValue);
		}
	}
}
/**
 * @package SOY2.SOY2HTML
 */
class DisplayPlugin extends PluginBase{
	var $soyValue;
	function executePlugin($soyValue){
		$this->soyValue = $soyValue;
	}
	function getStartTag(){
		return '<?php if(DisplayPlugin::toggle("'.$this->soyValue.'")){ ?>'. parent::getStartTag();
	}
	function getEndTag(){
		return  parent::getEndTag() . '<?php } ?>';
	}
	public static function visible($soyValue){
		DisplayPlugin::toggle($soyValue,1);
	}
	public static function toggle($soyValue,$flag = null){
		static $_flags;
		if(!$_flags){
			$_flags = array();
		}
		if(!is_null($flag)){
			$_flags[$soyValue] = $flag;
		}
		return (isset($_flags[$soyValue])) ? $_flags[$soyValue] : true;
	}
	public static function hide($soyValue){
		DisplayPlugin::toggle($soyValue,0);
	}
}
/**
 * @package SOY2.SOY2HTML
 */
class PanelPlugin extends PluginBase{
	var $soyValue;
	var $flag = true;
	function executePlugin($soyValue){
		$panels = &PanelPlugin::getPanels();
		$this->soyValue = $soyValue;
		if(in_array($soyValue,$panels)){
			$this->flag = true;
			$this->setInnerHTML("");
		}else{
			$panels[] = $soyValue;
			$this->flag = false;
		}
	}
	public static function &getPanels(){
		static $_panels;
		if(is_null($_panels)){
			$_panels = array();
		}
		return $_panels;
	}
	function getStartTag(){
		$html = array();
		if($this->flag){
			$html[] = '<?php echo $_panel_plugin_'.$this->soyValue.'; ?>';
		}else{
			$html[] = '<?php ob_start(); ?>';
		}
		return parent::getStartTag() . implode("\n",$html);
	}
	function getEndTag(){
		$html = array();
		if($this->flag){
		}else{
			$html[] = '<?php $_panel_plugin_'.$this->soyValue.' = ob_get_contents(); ?>';
			$html[] = '<?php ob_end_clean(); ?>';
			$html[] = '<?php echo $_panel_plugin_'.$this->soyValue.'; ?>';
		}
		return implode("\n",$html) . parent::getEndTag();
	}
}
/**
 * @package SOY2.SOY2HTML
 */
class IgnorePlugin extends PluginBase{
	function getStartTag(){
		return '<?php /* ?>';
	}
	function getEndTag(){
		return  '<?php */ ?>';
	}
}
/**
 * @package SOY2HTML
 */
class SOY2HTML_ControllPlugin extends PluginBase{
	function getStartTag(){
		$condition = $this->getAttribute("condition");
		$this->clearAttribute("condition");
		return '<?php $condition = ControllPlugin::checkCondition("'.$this->soyValue.'","'.htmlspecialchars($condition,ENT_QUOTES).'");' .
				'if($condition){ ?>' . parent::getStartTag();
	}
	public static function checkCondition($type,$key){
		switch($type){
			case "if":
			default:
				$res = false;
				if(strlen($key)>0){
					eval('$res = ('.$key.');');
				}
				return $res;
				break;
		}
		return false;
	}
	function getEndTag(){
		return  parent::getEndTag() . "<?php } ?>";
	}
}
/* SOY2HTML/SOY2HTMLComponents/WebPage.class.php */
/**
 * @package SOY2.SOY2HTML
 */
class WebPage extends HTMLPage{
	const SOY_TYPE = SOY2HTML::HTML_BODY;
	function __construct(){
		$this->init();
		$this->prepare();
	}
	/**
	 * prepareMethodにおいて
	 * Post時はこちらが呼ばれます。
	 *
	 */
	function doPost(){}
	/**
	 * doPostがよばれるように拡張
	 */
	function prepare(){
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			$this->doPost();
		}
		parent::prepare();
	}
	function getLayout(){
		return "default.php";
	}
}
/* SOY2HTML/SOY2HTMLComponents/functions.inc.php */
function soy2html_layout_include($file){
	$layoutDir = SOY2HTMLConfig::LayoutDir();
	@include($layoutDir . $file);
}
function soy2html_layout_get($file){
	try{
		ob_start();
		soy2html_layout_include($file);
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}catch(Exception $e){
		ob_end_flush();
		throw $e;
	}
}
/* SOY2Logic/SOY2Logic.php */
/**
 * @package SOY2.SOY2Logic
 */
interface SOY2LogicInterface{
	public static function getInstance($className,$args);
}
/**
 * @package SOY2.SOY2Logic
 */
abstract class SOY2LogicBase implements SOY2LogicInterface{
	public static function getInstance($className,$args){
		$obj = new $className();
		foreach($args as $key => $value){
			$method = "set".ucwords($key);
			if(method_exists($obj,$method)){
				$obj->$method($args[$key]);
			}
		}
		return $obj;
	}
}
/**
 * @package SOY2.SOY2Logic
 */
class SOY2Logic{
	public static function createInstance($classPath,$array = array()){
		if(!class_exists($classPath)){
			if(SOY2::import($classPath) == false){
				throw new Exception("Failed to include ".$classPath);
			}
		}
		if(preg_match('/\.?([a-zA-Z0-9_]+$)/',$classPath,$tmp)){
			$classPath = $tmp[1];
		}
		$refClass = new ReflectionClass($classPath);
		$interfaces = $refClass->getInterfaces();
		$flag = false;
		if(array_key_exists("SOY2LogicInterface",$interfaces)){
			$flag = true;
		}else{
			foreach($interfaces as $key => $interface){
				if($interface->getName() == "SOY2LogicInterface"){
					$flag = true;
					break;
				}
			}
		}
		if(!$flag){
			throw new Exception("[SOY2Logic]$classPath"." must be subclass of SOY2LogicBase.");
		}
		$method = $refClass->getMethod("getInstance");
		return $method->invoke(NULL,$classPath,$array);
	}
}
/**
 * @package SOY2.SOY2Logic
 */
class SOY2LogicContainer {
	private $logics = array();
	private function __construct(){
	}
	public static function get($name,$array = array()){
		static $instance;
		if(!$instance){
			$instance = new SOY2LogicContainer;
		}
		return $instance->_get($name,$array);
	}
    private function _get($name,$array = array()){
    	if(isset($this->logics[$name])){
    		$obj = $this->logics[$name];
    	}else{
    		$obj = SOY2Logic::createInstance($name,$array);
    		$this->logics[$name] = $obj;
    	}
    	foreach($array as $key => $value){
			$method = "set".ucwords($key);
			if(method_exists($obj,$method)){
				$obj->$method($array[$key]);
			}
		}
		return $obj;
    }
}
/* SOY2Logger/SOY2Logger.php */
/**
 * @package SOY2.SOY2Logger
 */
class SOY2Logger{
	const LEVEL_DEBUG =		0x1F;	//011111
	const LEVEL_INFO =		0x1E;	//011110
	const LEVEL_WARN = 		0x1C;	//011100
	const LEVEL_ERROR =		0x18;	//011000
	const LEVEL_FATAL =		0x10;	//010000
	const DEBUG		=	0x01;		//000001
	const INFO		=	0x02;		//000010
	const WARN		=	0x04;		//000100
	const ERROR		=	0x08;		//001000
	const FATAL		=	0x10;		//010000
	private $loggers = array();
	private $level = SOY2Logger::LEVEL_ERROR;	//ディフォルトはエラー以上
	private $startTime;
	private $stack;
	/**
	 * ロガーを追加
	 */
	public static function addLogger($id,$class, $options = null, $level = SOY2Logger::LEVEL_DEBUG){
		$logger = self::getLogger();
		if(is_object($class)){
			$obj = $class;
		}else{
			if(!class_exists($class)){
				$class = "SOY2Logger_" . $class;
			}
			if(!class_exists($class))return;
			$obj = new $class($options);
		}
		if($obj instanceof SOY2Logger_Base){
			$logger->loggers[$id] = array(
				"level" => $level,
				"logger" => $obj
			);
		}else{
			$logger->debug(get_class($obj) . " is not logger.");
		}
	}
	/**
	 * ディフォルトの出力レベルを設定する
	 */
	public static function setLevel($level){
		$logger = self::getLogger();
		$logger->level = $level;
	}
	/**
	 * ロガーオブジェクトを取得
	 */
	public static function getLogger(){
		static $_inst;
		if(!$_inst){
			$_inst = new SOY2Logger();
			$_inst->startTime = microtime(true);
			$logger = new SOY2Logger_Base();
			$_inst->loggers["SOY2Logger"] = array(
				"level" => SOY2Logger::DEBUG,
				"logger" => $logger
			);
		}
		return $_inst;
	}
	/**
	 * debugを出力する
	 */
	function debug($str){
		$this->log(SOY2Logger::DEBUG,"DEBUG",$str);
	}
	/**
	 * infoを出力する
	 */
	function info($str){
		$this->log(SOY2Logger::INFO,"INFO",$str);
	}
	/**
	 * warnを出力する
	 */
	function warn($str){
		$this->log(SOY2Logger::WARN,"WARN",$str);
	}
	/**
	 * errorを出力する
	 */
	function error($str){
		$this->log(SOY2Logger::ERROR,"ERROR",$str);
	}
	/**
	 * fatalを出力
	 */
	function fatal($str){
		$this->log(SOY2Logger::FATAL,"FATAL",$str);
	}
	/**
	 * ログを出力する
	 */
	function log($level,$levelText,$str){
		if(!($this->level & $level)){
			return;
		}
		$this->stack = $this->getStack();
		foreach($this->loggers as $id => $array){
			$loggerLevel = $array["level"];
			$logger = $array["logger"];
			if($loggerLevel & $this->level && $loggerLevel & $level){
				$log = $this->format($levelText,$str,$id,$logger);
				$logger->log($log);
			}
		}
	}
	/**
	 *
	 * フォーマット毎との置き換え
	 *
	 * 使えるフォーマット一覧
	 *
	 * %L	レベル
	 * %c	logger名
	 * %C	ログイベントが発生したクラスのクラス名
	 * %d	ログイベントが発生した時刻。%d{HH:mm:ss,SSS}のような指定が可能
	 * %F	ログイベントが発生したファイル名
	 * %l	ログ出力を行った行数
	 * %m	ログイベントで設定されたメッセージ
	 * %M	ログ出力を行ったメソッド名
	 * %n	プラットフォーム依存の改行
	 * %r	アプリケーションが開始してからログ出力されるまでの時間（単位：ミリ秒）
	 */
	function format($level,$str,$id,$logger){
		$format = $logger->format();
		$stack = $this->stack;
		$format = str_replace("%L",$level,$format);
		$format = str_replace("%c",$id,$format);
		$format = str_replace("%C",@$stack["Class"],$format);
		if(preg_match('/%d(\{(.+)\})?/',$format,$tmp)){
			if(!isset($tmp[2]) OR !$tmp[2])$tmp[2] = "Y:m:d H:i:s";
			$format = str_replace($tmp[0],date($tmp[2]),$format);
		}
		$format = str_replace("%F",$stack["FileName"],$format);
		$format = str_replace("%l",$stack["Line"],$format);
		$format = str_replace("%M",$stack["Method"],$format);
		$format = str_replace("%r",(microtime(true)-$this->startTime),$format);
		$format = str_replace("%n","\n",$format);
		$format = str_replace("%m",$str,$format);
		return $format;
	}
	/**
	 * logに出力する情報を取得する
	 */
	function getStack(){
		$array = debug_backtrace();
		$next = -1;
		foreach($array as $key => $stack){
			if( $stack["file"] == __FILE__
			 && $stack["function"] == "log"
			 && $stack["class"] == __CLASS__
			){
				$next = count($array) - $key - 1;
				continue;
			}
			if($next > 0){
				$next--;
				if($next != 0){
					continue;
				}
			}
			if($next == 0){
				return array(
					"FileName" => $stack["file"],
					"Class" => @$stack["class"],
					"Line" => $stack["line"],
					"Method" => $stack["function"]
				);
			}
		}
	}
}
/**
 * @package SOY2.SOY2Logger
 */
interface SOY2LoggerInterface{
	function log($str);
	function format();
}
/**
 *
 * ロガーのベース
 *
 * @package SOY2.SOY2Logger
 */
class SOY2Logger_Base implements SOY2LoggerInterface{
	/**
	 * ログを出力
	 */
	function log($str){
		echo $str . "\n";
	}
	/**
	 * フォーマット文字列を返す
	 */
	function format(){
		return "[%c][%L](%d) %m - %C#%M(%F:%l)";
	}
}
/**
 *
 * 汎用的なロガー
 *
 * @package SOY2.SOY2Logger
 */
class SOY2Logger_SimpleLogger extends SOY2Logger_Base{
	private $format;
	function __construct($options){
		if(isset($options["format"])){
			$this->format = $options["format"];
		}
	}
	function format(){
		return ($this->format) ? $this->format : parent::format();
	}
}
/**
 * 汎用的なファイル出力のためのロガー
 */
class SOY2Logger_FileLogger extends SOY2Logger_SimpleLogger{
	private $filePath;
	function __construct($options = array()){
		$this->setFilePath(@$options["path"]);
		parent::__construct($options);
	}
	/**
	 * ログを出力
	 */
	function log($str){
		$filepath = $this->getFilePath();
		if(strlen($filepath)>0)
			file_put_contents($filepath,$str . "\n", FILE_APPEND | LOCK_EX);
	}
	/**
	 * ファイルパスを取得
	 */
	function getFilePath() {
		return $this->filePath;
	}
	/**
	 * ファイルパスを設定
	 */
	function setFilePath($filePath) {
		$this->filePath = $filePath;
	}
}
class SOY2Logger_SOY2DebugLogger extends SOY2Logger_SimpleLogger{
	function __construct($option = array()){
		parent::__construct($option);
	}
	function log($str){
		SOY2Debug::trace($str);
	}
}
/**
 * 汎用的なファイル出力のためのロガー
 */
class SOY2Logger_RotationFileLogger extends SOY2Logger_FileLogger{
	private $maxLineCount = 400;
	private $maxFileCount = 10;
	private $suffix = "";
	function __construct($options){
		if(isset($options["line"]))$this->setMaxLineCount((int)$options["line"]);
		if(isset($options["count"]))$this->setMaxFileCount((int)$options["count"]);
		if(isset($options["suffix"]))$this->setSuffix((string)$options["suffix"]);
		parent::__construct($options);
	}
	/**
	 * ログを行います
	 */
	function log($str){
		$filepath = $this->getFilePath();
		$fdata = @file_get_contents($filepath);
		if(count(explode("\n",$fdata)) > $this->getMaxLineCount()){
			$this->rotation($filepath,$fdata);
		}
		parent::log($str);
	}
	/**
	 * ファイルのローテーションを実行する
	 */
	function rotation($filepath,$contents){
		$fp = fopen($filepath,"w");	//ここで0バイトになる
		flock($fp,LOCK_EX);
		$dirname = dirname($filepath)."/";
		$files = scandir($dirname);
		$logs = array();
		foreach($files as $file){
			if($file[0] == ".") continue;
			if(strpos($file,basename($filepath).$this->getSuffix().".") === 0){
				$logs[] = $file;
			}
		}
		$next_count = count($logs)+1;
		if($next_count < $this->getMaxFileCount()){
			$nextFilePath = basename($filepath).$this->getSuffix().".".$next_count;
			$logs[] = $nextFilePath;
		}
		$logs = array_reverse($logs);
		$logsCnt = count($logs)-1;
		for($i=0;$i<$logsCnt;++$i){
			@unlink($dirname.$logs[$i]);
			rename($dirname.$logs[($i+1)],$dirname.$logs[$i]);
		}
		@file_put_contents($filepath.$this->getSuffix().".1",$contents);
		flock($fp,LOCK_UN);
		fclose($fp);
	}
	function getMaxLineCount() {
		return $this->maxLineCount;
	}
	function setMaxLineCount($maxLineCount) {
		$this->maxLineCount = $maxLineCount;
	}
	function getMaxFileCount() {
		return $this->maxFileCount;
	}
	function setMaxFileCount($maxFileCount) {
		$this->maxFileCount = $maxFileCount;
	}
	function getSuffix() {
		return $this->suffix;
	}
	function setSuffix($suffix) {
		$this->suffix = $suffix;
	}
}
class SOY2Logger_DateFileLogger extends SOY2Logger_FileLogger{
	/**
	 * ファイルパスを取得
	 */
	function getFilePath() {
		$filepath = parent::getFilePath();
		return $filepath . "_" . date("Ymd");
	}
}
class SOY2Logger_DateRotationFileLogger extends SOY2Logger_RotationFileLogger{
	/**
	 * ファイルパスを取得
	 */
	function getFilePath() {
		$filepath = parent::getFilePath();
		return $filepath . "_" . date("Ymd");
	}
}
/* SOY2Plugin/SOY2Plugin.php */
/**
 * @package SOY2.SOY2Plugin
 */
interface SOY2PluginDelegateAction{
	function run($extetensionId,$moduleId,SOY2PluginAction $action);
}
/**
 * @package SOY2.SOY2Plugin
 */
interface SOY2PluginAction{}
/**
 * @package SOY2.SOY2Plugin
 */
class SOY2Plugin{
	/**
	 * 拡張ポイントを登録する
	 *
	 * @param string $extensionId 拡張ポイントID
	 * @param string $delegateClassName クラス名
	 */
	public static function registerExtension($extensionId, $delegateClassName){
		$inst =self::getInstance();
		$inst->setDelegate($extensionId,$delegateClassName);
	}
	/**
	 * 拡張ポイントを実行する
	 *
	 * @param string $extensionId 拡張ポイントID
	 * @param string $arguments オプション
	 */
	public static function invoke($extensionId, $arguments = array()){
		$inst = self::getInstance();
		$delegate = $inst->getDelegate($extensionId);
		if(!$delegate)return;
		SOY2::cast($delegate,(object)$arguments);
		$extensions = $inst->getExtensions($extensionId);
		/*
		 * delegateに処理を委譲
		 */
		foreach($extensions as $extensionId => $extensionArray){
			foreach($extensionArray as $moduleId => $array){
				foreach($array as $extensionClassName){
					$class = $inst->getClass($extensionClassName);
					if(!$class)continue;
					if(!($class instanceof SOY2PluginAction))return;
					$delegate->run($extensionId,$moduleId,$class);
				}
			}
		}
		return $delegate;
	}
	/**
	 * 拡張ポイントを実行する
	 *
	 * @return string
	 * @param string $extensionId 拡張ポイントID
	 * @param string $arguments オプション
	 */
	public static function display($extensionId, $arguments = array()){
		ob_start();
		self::invoke($extensionId,$arguments);
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}
	/**
	 * 拡張ポイントにモジュールを登録
	 *
	 * @param string $extensionId 拡張ポイント
	 * @param string $moduleId モジュールID
	 * @param string $className クラス名
	 */
	public static function extension($extensionId, $moduleId, $className){
		$inst =self::getInstance();
		$inst->addExtension($extensionId,$moduleId,$className);
	}
	/**
	 * Singleton
	 */
	public static function getInstance($className = null){
		static $_inst;
		if(is_null($_inst)){
			if(is_null($className))$className = "SOY2Plugin";
			$_inst = new $className();
		}
		return $_inst;
	}
	/*
	 * 以下内部使用メソッド、プロパティ
	 */
	private $delegates = array();
	private $extensions = array();
	private $objects = array();
	function setDelegate($point, $delegate){
		$this->delegates[$point] = $delegate;
	}
	/**
	 * @return SOY2PluginDelegateAction
	 */
	function getDelegate($point){
		if(!isset($this->delegates[$point]))return false;
		$delegateClassName = $this->delegates[$point];
		if(!class_exists($delegateClassName))return false;
		$delegate = new $delegateClassName();
		if(!($delegate instanceof SOY2PluginDelegateAction))return false;
		return $delegate;
	}
	/**
	 * 拡張ポイントに追加
	 */
	function addExtension($extension,$moduleId,$extensionClass){
		if(!isset($this->extensions[$extension]))$this->extensions[$extension] = array();
		if(!isset($this->extensions[$extension][$moduleId]))$this->extensions[$extension][$moduleId] = array();
		 $this->extensions[$extension][$moduleId][] = $extensionClass;
	}
	/**
	 *
	 */
	function getClass($className){
		if(!class_exists($className)){
			return null;
		}
		if(!isset($this->classes[$className])){
			$obj = new $className();
			$this->classes[$className] = $obj;
		}
		return $this->classes[$className];
	}
	function getDelegates() {
		return $this->delegates;
	}
	function setDelegates($delegates) {
		$this->delegates = $delegates;
	}
	function getExtensions($extensionId = null) {
		if(!is_null($extensionId)){
			if(strpos($extensionId,".*") == strlen($extensionId)-2){
				$extensionId = substr($extensionId,0,strlen($extensionId)-1);
				$res = array();
				foreach($this->extensions as $key => $array){
					if(strpos($key,$extensionId) === 0){
						$res[$key] = $array;
					}
				}
				return $res;
			}else{
				return (isset($this->extensions[$extensionId])) ? array($extensionId => $this->extensions[$extensionId]) : array();
			}
		}
		return $this->extensions;
	}
	function setExtensions($extensions) {
		$this->extensions = $extensions;
	}
	function getObjects() {
		return $this->objects;
	}
	function setObjects($objects) {
		$this->objects = $objects;
	}
}
/* SOY2Session/SOY2Session.class.php */
class SOY2Session{
	const _KEY_ = "_soy2_session_";
	private static $_deleted_class = null;
	/**
	 * get session
	 */
	public static final function get($sessionClass){
		return self::getSession($sessionClass)->getObject();
	}
	public static function getSession($sessionClass){
		$className = SOY2::import($sessionClass);
		if(!isset($_SESSION)){
			session_start();
		}
		if(!isset($_SESSION[self::_KEY_]))$_SESSION[self::_KEY_] = array();
		if(!isset($_SESSION[self::_KEY_][$className])){
			$obj = new SOY2SessionValue($sessionClass);
			$_SESSION[self::_KEY_][$className] = $obj;
		}else{
			$obj = $_SESSION[self::_KEY_][$className];
		}
		return $_SESSION[self::_KEY_][$className];
	}
	public static function destroyAll(){
		if(!isset($_SESSION)){
			session_start();
		}
		unset($_SESSION[self::_KEY_]);
	}
	public static function destroySession($sessionClass = null){
		if(!$sessionClass){
			return self::$_deleted_class;
		}
		$className = preg_replace('/.*\.(.*)/','$1',$sessionClass);
		if(!isset($_SESSION)){
			self::$_deleted_class = $className;
			session_start();
		}
		$_SESSION[self::_KEY_][$className] = null;
		unset($_SESSION[self::_KEY_][$className]);
	}
	/**
	 * init
	 * call only first time
	 */
	function init(){
	}
	/**
	 * 復元時に毎回呼ばれる
	 */
	function wakeup(){
	}
	/**
	 * delete from session
	 */
	function destroy(){
		unset($_SESSION[self::_KEY_][get_class($this)]);
	}
	/**
	 * reset all parameter to null
	 */
	function clear(){
		$_SESSION[self::_KEY_][get_class($this)]->reset();
	}
}
class SOY2SessionValue{
	private $create;
	private $update;
	private $className;
	private $classObject;
	private $classValue;
	function __construct($className){
		$class = SOY2::import($className);
		$this->className = $className;
		$this->classObject = new $class;
		$this->create = time();
		if(!$this->classObject instanceof SOY2Session){
			trigger_error($className . " is not subclass of SOY2Session");
		}
		$this->classObject->init();
	}
	function getClassName() {
		return $this->className;
	}
	function setClassName($className) {
		$this->className = $className;
	}
	function getClassValue() {
		return $this->classValue;
	}
	function setClassValue($classValue) {
		$this->classValue = $classValue;
	}
	function getObject(){
		return $this->classObject;
	}
	function __sleep(){
		if($this->classObject){
			$this->classValue = SOY2::cast("object",$this->classObject);
		}
		$this->update = time();
		return array("className","classValue","create","update");
	}
	function __wakeup(){
		try{
			$this->classObject = SOY2::cast($this->className,$this->classValue);
			if(SOY2Session::destroySession() != get_class($this->classObject)){
				$this->classObject->wakeup();
			}
		}catch(Exception $e){
		}
	}
	function reset(){
		$obj = SOY2::cast("array",$this->classObject);
		foreach($obj as $key => $value){
			$obj[$key] = null;
		}
		$this->classObject = SOY2::cast($this->className,(object)$obj);
	}
}
/* function/function.soy2_cancel_magic_quotes_gpc.php */
function soy2_cancel_magic_quotes_gpc(){
	if(get_magic_quotes_gpc()){
		$_POST = soy2_stripslashes($_POST);
		$_GET = soy2_stripslashes($_GET);
		$_COOKIE = soy2_stripslashes($_COOKIE);
		$_REQUEST = soy2_stripslashes($_REQUEST);
	}
}
function soy2_stripslashes($value){
	return is_array($value) ? array_map('soy2_stripslashes', $value) : stripslashes($value);
}
/* function/function.soy2_image.php */
/*
 * soy2_image_info
 * @param String filepath
 * @return Array("width" => int, "height" => int)
 * 指定した画像の幅と高さを返す
 */
function soy2_image_info($filepath){
	if(!is_readable($filepath) || is_dir($filepath)){
		return false;
	}
	/*
	 * GD
	 * http://php.net/manual/en/book.image.php
	 */
	if(function_exists("getimagesize")){
		$imageSize = getimagesize($filepath);
		return array("width" => $imageSize[0], "height" => $imageSize[1]);
	}
	/*
	 * Image Magick
	 * http://php.net/manual/en/book.imagick.php
	 */
	if(class_exists("Imagick")){
		$thumb = new Imagick($filepath);
		return array("width" => $thumb->getImageWidth(), "height" => $thumb->getImageHeight());
	}
	/*
	 * Gmagick
	 * http://php.net/manual/en/book.gmagick.php
	 */
	if(class_exists("Gmagick")){
		$thumb = new Gmagick($filepath);
		return array("width" => $thumb->getimagewidth(), "height" => $thumb->getimageheight());
	}
	/*
	 * NewMagickWand
	 * http://www.magickwand.org/
	 */
	if(function_exists("NewMagickWand")){
		$thumb = NewMagickWand();
		MagickReadImage($thumb,$filepath);
		return array("width" => MagickGetImageWidth($thumb), "height" => MagickGetImageHeight($thumb));
	}
	return null;
}
/* function/function.soy2_path2url.php */
/**
 * PathをURLに変換
 */
function soy2_path2url($path){
	$path = soy2_realpath($path);
	$root = soy2_realpath($_SERVER["DOCUMENT_ROOT"]);
	$url = str_replace($root,"/",$path);
	return $url;
}
/* function/function.soy2_realpath.php */
/**
 * realpath 末尾が必ず「/」で返値
 */
function soy2_realpath($dir){
	$path = realpath($dir);
	if(!$path)return $path;
	$path = str_replace("\\","/",$path);
	if(is_dir($path) && $path[strlen($path)-1] != "/")$path .= "/";
	return $path;
}
/**
 * URLの末尾をスラッシュで終わらせるか
 */
function soy2_realurl($url){
	//末尾が拡張子の場合はそのまま
	$arg = substr($url, strrpos($url, "/") + 1);
	if(!strlen($arg) || $arg == "_notfound") return $url;
	if(preg_match('/\.html$|\.htm$|\.xml$|\.css$|\.js$|\.json$|\.php$/i', $arg)) return $url;
	return $url . "/";
}
/* function/function.soy2_require.php */
/**
 * include file(replace require(*))
 * @param path
 * @param when true do not return boolean value
 * @return boolean
 */
function soy2_require($file,$isThrowException = false){
	$res = (boolean)@include_once($file);
	if($isThrowException && !$res)throw new Exception("File Not Found:" . $file);
	return $res;
}
/* function/function.soy2_resizeimage.php */
/*
 * Created on 2010/04/28
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
/**
 * 縦横の最大の大きさ指定してリサイズ
 */
function soy2_resizeimage_maxsize($filepath,$savepath,$max){
	if(function_exists("getimagesize")){
		list($width, $height, $type, $attr) = getimagesize($filepath);
	}
	else if(class_exists("Imagick")){
		$thumb = new Imagick($filepath);
		$width = $thumb->getImageWidth();
		$height = $thumb->getImageHeight();
		$thumb = null;
	}
	else if(function_exists("NewMagickWand")){
		$thumb = NewMagickWand();
		MagickReadImage($thumb,$filepath);
		list($width,$height) = array(MagickGetImageWidth($thumb),MagickGetImageHeight($thumb));
		$thumb = null;
	}
	else{
		throw new Exception("soy2_resizeimage_maxsize is not avaiable.please install Imagick,NewMagickWand or GD");
	}
	if($width <= $max AND $height <= $height){
		return soy2_resizeimage($filepath,$savepath,$width,$height);
	}
	if($width > $height){
		$width = $max;
		$height = null;
	}else{
		$width = null;
		$height = $max;
	}
	return soy2_resizeimage($filepath,$savepath,$width,$height);
}
/**
 * 縦横の大きさ指定してリサイズ
 *
 * @param $filepath
 * @param $savepath
 * @param $width
 * @param $height
 */
function soy2_resizeimage($filepath,$savepath,$width = null,$height = null){
	if(class_exists("Imagick")){
		$thumb = new Imagick($filepath);
		$imageSize = array($thumb->getImageWidth(),$thumb->getImageHeight());
		if(is_null($width) && is_null($height)){
			$width = $imageSize[0];
			$height = $imageSize[1];
		}else if(is_null($width)){
			$width = $imageSize[0] * $height / $imageSize[1];
		}else if(is_null($height)){
			$height = $imageSize[1] * $width / $imageSize[0];
		}
		$thumb->thumbnailImage($width,$height);
		$thumb->writeImage($savepath);
		return true;
	}
	if(function_exists("NewMagickWand")){
		$thumb = NewMagickWand();
		MagickReadImage($thumb,$filepath);
		$imageSize = array(MagickGetImageWidth($thumb),MagickGetImageHeight($thumb));
		if(is_null($width) && is_null($height)){
			$width = $imageSize[0];
			$height = $imageSize[1];
		}else if(is_null($width)){
			$width = $imageSize[0] * $height / $imageSize[1];
		}else if(is_null($height)){
			$height = $imageSize[1] * $width / $imageSize[0];
		}
		if(!MagickResizeImage($thumb,$width,$height,MW_LanczosFilter,1)){
			trigger_error("Failed [MagickResizeImage] " . __FILE__ . ":" . __LINE__,E_USER_ERROR);
			return -1;
		}
		if(!MagickWriteImage($thumb,$savepath)){
			trigger_error("Failed [MagickWriteImage] " . __FILE__ . ":" . __LINE__,E_USER_ERROR);
			return -1;
		}
		return true;
	}
	return soy2_image_resizeimage_gd($filepath,$savepath,$width,$height);
}
function soy2_image_resizeimage_gd($filepath,$savepath,$width = null,$height = null){
	$info = pathinfo($filepath); //php version is 5.2.0 use pathinfo($filepath,PATHINFO_EXTENSION);
	if(!isset($info["extension"])) {
		trigger_error("Failed [Type is empty] " . __FILE__ . ":" . __LINE__,E_USER_ERROR);
		return -1;
	}
	$type = strtolower($info["extension"]);
	if($type == "jpg")$type = "jpeg";
	$from = "imagecreatefrom" . $type;
	if(!function_exists($from)){
		trigger_error("Failed [Invalid Type:".$type."] " . __FILE__ . ":" . __LINE__,E_USER_ERROR);
		return -1;
	}
	$srcImage = $from($filepath);
	$imageSize = getimagesize($filepath);
	if(is_null($width) && is_null($height)){
		$width = $imageSize[0];
		$height = $imageSize[1];
	}else if(is_null($width)){
		$width = $imageSize[0] * $height / $imageSize[1];
	}else if(is_null($height)){
		$height = $imageSize[1] * $width / $imageSize[0];
	}
	$dstImage = imagecreatetruecolor($width,$height);
	imagecopyresampled($dstImage,$srcImage, 0, 0, 0, 0,
  			$width, $height, $imageSize[0], $imageSize[1]);
  	$info = pathinfo($savepath); //php version is 5.2.0 use pathinfo($filepath,PATHINFO_EXTENSION);
	$type = strtolower($info["extension"]);;
	switch($type){
		case "jpg":
		case "jpeg":
			return imagejpeg($dstImage,$savepath,100);
			break;
		default:
			$to = "image" . $type;
			if(function_exists($to)){
				$to($dstImage,$savepath);
				return true;
			}
			trigger_error("Failed [Invalid Type:".$type."] " . __FILE__ . ":" . __LINE__,2);
			return -1;
			break;
	}
}
/* function/function.soy2_scandir.php */
/**
 * 「.」から始まるディレクトリを取り除いたscandir
 *
 * @param $dir ディレクトリ
 */
function soy2_scandir($dir){
	$res = array();
	$files = scandir($dir);
	foreach($files as $row){
		if($row[0] == ".")continue;
		$res[] = $row;
	}
	return $res;
}
/* function/function.soy2_scanfiles.php */
/**
 * soy2_scanfiles
 * 特定のディレクトリの下にあるファイルを全て列挙
 */
function soy2_scanfiles($dir,$depth = -1){
	$res = array();
	$dir = soy2_realpath($dir);
	if($depth == 0)return $res;
	$files = soy2_scandir($dir);
	foreach($files as $file){
		if(is_dir($dir . $file)){
			$res = array_merge($res,soy2_scanfiles($dir . $file,($depth-1)));
		}else{
			$res[] = $dir . $file;
		}
	}
	return $res;
}
/* function/function.soy2_serialize.php */
/**
 * serializeしたあとにaddslashesを行う
 *
 * @param $var 配列やインスタンスなど
 */
function soy2_serialize($var){
	return addslashes(serialize($var));
}
/**
 * stripslashesしてからunserializeを行う
 *
 * @param $string soy2_serializeの出力する文字列
 */
function soy2_unserialize($string){
	return (is_string($string)) ? unserialize(stripslashes($string)) : null;
}
/* function/function.soy2_token.php */
/*
 * tokenを発行など
 */
function soy2_get_token(){
	if(session_status() == PHP_SESSION_NONE) session_start();
	if(!isset($_SESSION["soy2_token"])){
		$_SESSION["soy2_token"] = soy2_generate_token();
	}
	return $_SESSION["soy2_token"];
}
function soy2_check_token(){
	if(session_status() == PHP_SESSION_NONE) session_start();
	if(isset($_SESSION["soy2_token"]) && isset($_REQUEST["soy2_token"])){
		if($_REQUEST["soy2_token"] === $_SESSION["soy2_token"]){
			$_SESSION["soy2_token"] = soy2_generate_token();
			return true;
		}
	}
	return false;
}
function soy2_generate_token(){
	return md5(mt_rand());
}
function soy2_check_referer(){
	$referer = parse_url($_SERVER['HTTP_REFERER']);
	$port = (isset($referer["port"]) && ($referer["port"] != 80 || $referer["port"] != 443)) ? ":" . $referer["port"] : "";
	if($referer['host'] . $port !== $_SERVER['HTTP_HOST']) return false;

	$_path = $referer["path"];

	//pathinfoがある時は削除する
	$queryString = $_SERVER['QUERY_STRING'];
	if(is_numeric(strpos($queryString, "pathinfo"))){
		$array = explode("&", $queryString);
		$params = array();
		foreach($array as $value){
			$v = explode("=", $value);
			if($v[0] == "pathinfo") continue;
			$params[$v[0]] = $v[1];
		}

		$queryString = "";
		if(count($params)){
			foreach($params as $key => $v){
				if(strlen($queryString)) $queryString .= "&";
				$queryString .= $key . "=" . $v;
			}
		}
	}

	if(isset($queryString) && strlen($queryString)) $_path .= "?" . $queryString;
	return ($_path == $_SERVER['REQUEST_URI']);
}

/* function/function.soy2_setcookie.php */
/*
 * PHPのバージョンによってsetcookieのオプションの値を変える
 */
function soy2_setcookie($key, $value=null, $opts=array()){
	if(!count($opts)) $opts = session_get_cookie_params();	//optsが空の場合はセッションの設定を用いる
	if(is_null($value))	$opts["expires"] = time()-1;	//valueがnullの場合はクッキーを削除する
	if(isset($opts["lifetime"])) unset($opts["lifetime"]);	//lifetimeがある場合は削除

	if(!isset($opts["path"]) || !is_string($opts["path"]) || !strlen($opts["path"])) $opts["path"] = "/";
	if(!isset($opts["domain"]) || !is_string($opts["domain"]) || !strlen($opts["domain"])) $opts["domain"] = null;
	if(!isset($opts["expires"]) || !is_numeric($opts["expires"])) $opts["expires"] = 0;
	if(!isset($opts["httponly"]) || !is_bool($opts["httponly"])) $opts["httponly"] = true;
	if(!isset($opts["secure"]) || !is_bool($opts["secure"])) $opts["secure"] = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on");

	$vArr = explode(".", phpversion());
	if(($vArr[0] >= 8 || ($vArr[0] >= 7 && $vArr[1] >= 3))){	//php 7.3以降 samesiteの指定が出来る
		if(!isset($opts["samesite"])) {	// SameSiteの値はセッションの設定から取得する
			$sessParams = session_get_cookie_params();
			$opts["samesite"] = (isset($sessParams["samesite"]) && strlen($sessParams["samesite"])) ? $sessParams["samesite"] : "Lax";
			unset($sessParams);
		}
		setcookie($key, $value, $opts);
	}else{
		setcookie($key, $value , $opts["expires"], $opts["path"], $opts["domain"], $opts["secure"], $opts["httponly"]);
	}
}

/* function/function.soy2_number_format.php */
/*
 * number_formatの第一引数が数字ではなかった場合
 */
function soy2_number_format($int){
	if(!is_numeric($int)) return 0;
	return number_format($int);
}
