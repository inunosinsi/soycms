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
