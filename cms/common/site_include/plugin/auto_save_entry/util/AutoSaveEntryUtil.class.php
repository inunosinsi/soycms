<?php

class AutoSaveEntryUtil{

	public static function getDirectory($loginId){
		$dir = UserInfoUtil::getSiteDirectory() . ".entry/";
		if(!file_exists($dir)){
			mkdir($dir);
			file_put_contents($dir . ".htaccess", "deny from all");
		}
		return $dir . $loginId . "/";
	}

	public static function checkBackupFile($loginId){
		$dir = UserInfoUtil::getSiteDirectory() . ".entry/" . $loginId . "/";
		return (file_exists($dir . "title.backup") || file_exists($dir . "content.backup") || file_exists($dir . "more.backup"));
	}

	public static function deleteBackup($loginId){
		$dir = UserInfoUtil::getSiteDirectory() . ".entry/" . $loginId . "/";
		if(file_exists($dir . "title.backup")) unlink($dir . "title.backup");
		if(file_exists($dir . "content.backup")) unlink($dir . "content.backup");
		if(file_exists($dir . "more.backup")) unlink($dir . "more.backup");
	}
}
