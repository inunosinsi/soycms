<?php

// Enforce HTTPOnly to PHPSESSID
// XSS cannot get session id with this option
ini_set('session.cookie_httponly', 1);

ini_set("short_open_tag","Off");

mb_language('Japanese');

//PHPのバージョンによる条件分岐 配列の0番目と1番目でバージョンを表す
$vArr = explode(".", phpversion());

//5.5よりも上のバージョン
if($vArr[0] . $vArr[1] > 55){
	ini_set("default_charset","UTF-8");
//5.5以前のバージョン
}else{
	ini_set("mbstring.internal_encoding","UTF-8");
}


ini_set("display_errors","On");
ini_set("log_errors",1);
//error_logを指定しなければApacheのログに残る
if(is_dir(dirname(dirname(__FILE__))."/log") && is_writable(dirname(dirname(__FILE__))."/log")){
	ini_set("error_log",dirname(dirname(__FILE__))."/log/error-".date("Ym").".log");
}

