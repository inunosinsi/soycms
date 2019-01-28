<?php

if(isset($argv[1])){
	$shopId = $argv[1];
	$limit = (isset($argv[2]) && is_numeric($argv[2])) ? (int)$argv[2] : 500;

	chdir(dirname(__FILE__));
	$soyshopWebapp = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))));

	include($soyshopWebapp . "/conf/common.conf.php");
	include($soyshopWebapp . "/conf/shop/" .$shopId . ".conf.php");

	soyshop_load_db_config();

	SOY2Logic::createInstance("module.plugins.order_table_index.logic.OptimizeLogic")->optimize($limit);
}
