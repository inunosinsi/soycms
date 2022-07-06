<?php
set_time_limit(0);

//soy2 etc
$commonDir = dirname(dirname(dirname(dirname(dirname(__FILE__)))));
include_once($commonDir . "/soycms.config.php");
include_once($commonDir . "/common.inc.php");
include_once($commonDir . "/config/db/" . SOYCMS_DB_TYPE . ".php");

SOY2DAOConfig::dsn(ADMIN_DB_DSN);
SOY2DAOConfig::user(ADMIN_DB_USER);
SOY2DAOConfig::pass(ADMIN_DB_PASS);

SOY2::import("util.CMSUtil");
SOY2Logic::createInstance("logic.cache.CacheLogic")->clearCache();
