<?php
define("SOYCMS_DB_TYPE","mysql");
define("SOYCMS_VERSION","3.2.0.10");
define("SOYCMS_BUILD","2021-12-15T10:39:20+09:00");
define("SOYCMS_BUILD_TIME","1639532360");
define("SOYCMS_REVISION","45154");
define("SOYCMS_AUTOLOGIN_EXPIRE", 30);
if(!defined("SOY2HTML_CACHE_FORCE")) define("SOY2HTML_CACHE_FORCE", false);

//高度な設定
if(file_exists(dirname(__FILE__) . "/config/advanced.config.php")){
	include_once(dirname(__FILE__) . "/config/advanced.config.php");
}
