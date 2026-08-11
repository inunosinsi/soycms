<?php
//$argv[1]にshopIdが入ってる
if(!isset($argv[1])) return;

chdir(dirname(__FILE__));
$soyshopWebapp = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))));
	
include($soyshopWebapp . "/conf/common.conf.php");
include($soyshopWebapp . "/conf/shop/" . trim($argv[1]) . ".conf.php");
	
soyshop_load_db_config();

SOY2Logic::createInstance("module.plugins.common_sale_period.logic.NotificationMailLogic")->execute();
?>