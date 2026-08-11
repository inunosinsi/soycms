<?php

class CampaignUtil {

	const MODE_LIST = "list";
	const MODE_ENTRY = "entry";

	public static function getDirectory($loginId){
		$dir = SOYSHOP_SITE_DIRECTORY . ".entry/";
		if(!file_exists($dir)){
			mkdir($dir);
			file_put_contents($dir . ".htaccess", "deny from all");
		}
		return $dir . $loginId . "/";
	}

	public static function checkBackupFile($loginId){
		$dir = SOYSHOP_SITE_DIRECTORY . ".entry/" . $loginId . "/";
		return (file_exists($dir . "title.backup") || file_exists($dir . "content.backup"));
	}

	public static function deleteBackup($loginId){
		$dir = SOYSHOP_SITE_DIRECTORY . ".entry/" . $loginId . "/";
		if(file_exists($dir . "title.backup")) unlink($dir . "title.backup");
		if(file_exists($dir . "content.backup")) unlink($dir . "content.backup");
	}
}
