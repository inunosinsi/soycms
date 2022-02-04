<?php
define("SOYCMS_DB_TYPE","mysql");
define("SOYCMS_VERSION","3.2.2.1");
define("SOYCMS_BUILD","2022-02-04T13:38:08+09:00");
define("SOYCMS_BUILD_TIME","1643949489");
define("SOYCMS_REVISION","45154");
define("SOYCMS_AUTOLOGIN_EXPIRE", 30);
if(!defined("SOY2HTML_CACHE_FORCE")) define("SOY2HTML_CACHE_FORCE", false);

//高度な設定
if(file_exists(dirname(__FILE__) . "/config/advanced.config.php")){
	include_once(dirname(__FILE__) . "/config/advanced.config.php");
}
