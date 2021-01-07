<?php

//soy2 etc
$commonDir = dirname(dirname(dirname(dirname(dirname(__FILE__)))));
include_once($commonDir . "/soycms.config.php");
include_once($commonDir . "/common.inc.php");
include_once($commonDir . "/config/db/" . SOYCMS_DB_TYPE . ".php");

SOY2DAOConfig::dsn(ADMIN_DB_DSN);
SOY2DAOConfig::user(ADMIN_DB_USER);
SOY2DAOConfig::pass(ADMIN_DB_PASS);

try{
	$sites = SOY2DAOFactory::create("admin.SiteDAO")->get();
}catch(Exception $e){
	$sites = array();
}

if(count($sites)){
	SOY2::import("util.CMSUtil");
	foreach($sites as $site){
		CMSUtil::unlinkAllIn($site->getPath() . ".cache/", true);
	}
}
