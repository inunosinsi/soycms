<?php

//ショップIDを引数に入れて実行
if(isset($argv[1])){
	$shopId = $argv[1];
	
	chdir(dirname(__FILE__));
	$soyshopWebapp = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))));
	
	include($soyshopWebapp . "/conf/common.conf.php");
	include($soyshopWebapp . "/conf/shop/" .$shopId . ".conf.php");
	
	soyshop_load_db_config();
	
	$calcLogic = SOY2Logic::createInstance("module.plugins.common_auto_ranking.logic.CalculateRankingLogic");
	$calcLogic->execute();
}
?>