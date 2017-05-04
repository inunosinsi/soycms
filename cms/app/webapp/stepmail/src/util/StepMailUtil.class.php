<?php

class StepMailUtil{
	
	public static function getDirectory($loginId){
		//連携しているショップの方にバックアップを作る		
		$dir = SOYSHOP_SITE_DIRECTORY . ".stepmail/";
		if(!file_exists($dir)){
			mkdir($dir);
			file_put_contents($dir . ".htaccess", "deny from all");
		}
		return $dir . $loginId . "/";
	}
	
	public static function checkBackupFile($loginId){
		$dir = SOYSHOP_SITE_DIRECTORY . ".stepmail/" . $loginId . "/";
		return (file_exists($dir . "title.backup") || file_exists($dir . "content.backup") || file_exists($dir . "more.backup"));
	}
	
	public static function deleteBackup($loginId){
		$dir = SOYSHOP_SITE_DIRECTORY . ".stepmail/" . $loginId . "/";
		if(file_exists($dir . "title.backup")) unlink($dir . "title.backup");
		if(file_exists($dir . "overview.backup")) unlink($dir . "overview.backup");
		if(file_exists($dir . "content.backup")) unlink($dir . "content.backup");
	}
}
?>