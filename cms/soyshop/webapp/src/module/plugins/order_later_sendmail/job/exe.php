<?php
if(isset($argv[1])){
	$shopId = $argv[1];
	
	chdir(dirname(__FILE__));
	$soyshopWebapp = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))));
	
	include($soyshopWebapp . "/conf/common.conf.php");
	include($soyshopWebapp . "/conf/shop/" .$shopId . ".conf.php");
	
	soyshop_load_db_config();
	
	$SendmailLogic = SOY2Logic::createInstance("module.plugins.order_later_sendmail.logic.SendmailLogic");
	$SendmailLogic->execute();
}
?>