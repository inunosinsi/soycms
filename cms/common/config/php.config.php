<?php
// Enforce HTTPOnly to PHPSESSID
// XSS cannot get session id with this option → migrate session.config.php
//ini_set('session.cookie_httponly', 1);

if(file_exists(dirname(__FILE__) . "/session.config.php")){
	include_once(dirname(__FILE__) . "/session.config.php");
}else{
	include_once("session.default.config.php");
}

ini_set("short_open_tag","Off");

mb_language('Japanese');

//PHPのバージョンによる条件分岐 配列の0番目と1番目でバージョンを表す
//5.5よりも上のバージョン
$vArr = explode(".", phpversion());
if($vArr[0] . $vArr[1] > 55){
	ini_set("default_charset","UTF-8");
//5.5以前のバージョン
}else{
	ini_set("mbstring.internal_encoding","UTF-8");
}
unset($vArr);

ini_set("display_errors","On");
ini_set("log_errors",1);
//error_logを指定しなければApacheのログに残る
if(is_dir(dirname(dirname(__FILE__))."/log") && is_writable(dirname(dirname(__FILE__))."/log")){
	ini_set("error_log",dirname(dirname(__FILE__))."/log/error-".date("Ym").".log");
}

// 各種共有サーバのSSLの設定
if(!isset($_SERVER["HTTPS"])){
	if(
		isset($_SERVER["HTTP_X_SAKURA_FORWARDED_FOR"]) ||	//さくらインターネット
		(isset($_SERVER["HTTP_X_FORWARDED_PROTO"]) && $_SERVER["HTTP_X_FORWARDED_PROTO"] == "https")	//onamae.com
	){
		$_SERVER["HTTPS"] = "on";
		$_SERVER["SERVER_PORT"] = "443";
	}
}

// $_SERVER["REQUEST_URI"]の整形
if(isset($_SERVER["REQUEST_URI"])){
	for(;;){
		$_pos = strpos($_SERVER["REQUEST_URI"], "//");
		if(is_bool($_pos) || $_pos > 0) {
			unset($_pos);
			break;
		}
		$_SERVER["REQUEST_URI"] = substr($_SERVER["REQUEST_URI"], 1);
	}	
}

// NGINX対応
if(!isset($_SERVER["REDIRECT_URL"])){
	$_SERVER["REDIRECT_URL"] = (isset($_SERVER["REQUEST_URI"]) && strlen($_SERVER["REQUEST_URI"])) ? $_SERVER["REQUEST_URI"] : "/";
	if(strlen($_SERVER["REDIRECT_URL"]) > 1){
		foreach(array("?", "#") as $_sym){
			if(is_numeric(strpos($_SERVER["REDIRECT_URL"], $_sym))) $_SERVER["REDIRECT_URL"] = substr($_SERVER["REDIRECT_URL"], 0, strpos($_SERVER["REDIRECT_URL"], $_sym));
		}
	}
}

// NGINX対応
if(!isset($_SERVER["HTTP_HOST"]) || !is_string($_SERVER["HTTP_HOST"])) {
	$_SERVER["HTTP_HOST"] = (isset($_SERVER["SERVER_NAME"])) ? $_SERVER["SERVER_NAME"] : "";
}

// NGINX対応
if((!isset($_SERVER["SCRIPT_NAME"]) || is_bool(strpos($_SERVER["SCRIPT_NAME"], "index.php"))) && isset($_SERVER["SCRIPT_FILENAME"])){
	$_SERVER["SCRIPT_NAME"] = "/" . ltrim(str_replace($_SERVER["DOCUMENT_ROOT"], "", $_SERVER["SCRIPT_FILENAME"]), "/");
}

// PATH_INFOを補完する SOYCMS_PHP_CGI_MODE(php_sapi_name() == "cgi")対策
if(isset($_GET["pathinfo"])){
	$_SERVER["PATH_INFO"] = "/".$_GET["pathinfo"];
	unset($_GET["pathinfo"]);
}

if(!isset($_SERVER["PATH_INFO"])){
	// 公開側
	if(defined("_SITE_ROOT_") || (defined("DISPLAY_SOYSHOP_SITE") && DISPLAY_SOYSHOP_SITE)){
		if($_SERVER["SCRIPT_NAME"] === "/index.php"){
			$_SERVER["PATH_INFO"] = $_SERVER["REDIRECT_URL"];
		}else{
			$_SERVER["PATH_INFO"] = "/".ltrim(str_replace(trim(str_replace("index.php", "", $_SERVER["SCRIPT_NAME"])), "", $_SERVER["REDIRECT_URL"]), "/");
		}
		
	// 管理画面側
	}else{
		if(is_numeric(strpos($_SERVER["REDIRECT_URL"], "/index.php"))){
			$_SERVER["PATH_INFO"] = "/".ltrim(substr($_SERVER["REDIRECT_URL"], strpos($_SERVER["REDIRECT_URL"], "index.php")+strlen("index.php")), "/");
		}
	}

	// $_SERVER["PATH_INFO"]が / のみの場合は削除
	if(isset($_SERVER["PATH_INFO"]) && strlen($_SERVER["PATH_INFO"]) <= 1) unset($_SERVER["PATH_INFO"]);
}
