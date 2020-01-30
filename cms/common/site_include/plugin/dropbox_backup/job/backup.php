<?php
if(isset($argv[1])){
	$siteId = $argv[1];

	chdir(dirname(__FILE__));
	include_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))) . "/common.inc.php");

	SOY2Logic::createInstance("site_include.plugin.dropbox_backup.logic.DropboxBackupLogic")->backup($siteId);
}
