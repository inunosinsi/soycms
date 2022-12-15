<?php
define("SOYCMS_DB_TYPE","mysql");
define("SOYCMS_VERSION","3.8.12");
define("SOYCMS_BUILD","2022-12-15T11:16:30+09:00");
define("SOYCMS_RAW",20221215111630);	//Apache Ant(JDK17)対策 ここでしか使わない定数
define("SOYCMS_BUILD_TIME", mktime(substr(SOYCMS_RAW, 8, 2), substr(SOYCMS_RAW, 10, 2), substr(SOYCMS_RAW, 12), substr(SOYCMS_RAW, 4, 2), substr(SOYCMS_RAW, 6, 2), substr(SOYCMS_RAW, 0, 4)));
define("SOYCMS_REVISION","45154");
define("SOYCMS_AUTOLOGIN_EXPIRE", 30);
if(!defined("SOY2HTML_CACHE_FORCE")) define("SOY2HTML_CACHE_FORCE", false);

//高度な設定
if(file_exists(dirname(__FILE__) . "/config/advanced.config.php")){
	include_once(dirname(__FILE__) . "/config/advanced.config.php");
}
