<?php
define("SOYCMS_DB_TYPE","mysql");
define("SOYCMS_VERSION","3.5.8");
define("SOYCMS_BUILD","2022-08-03T11:24:25+09:00");
define("SOYCMS_BUILD_TIME","1659493468");
define("SOYCMS_REVISION","45154");
define("SOYCMS_AUTOLOGIN_EXPIRE", 30);
if(!defined("SOY2HTML_CACHE_FORCE")) define("SOY2HTML_CACHE_FORCE", false);

//高度な設定
if(file_exists(dirname(__FILE__) . "/config/advanced.config.php")){
	include_once(dirname(__FILE__) . "/config/advanced.config.php");
}
