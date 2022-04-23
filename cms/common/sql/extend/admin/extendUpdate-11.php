<?php
if(!defined("CMS_COMMON")) define("CMS_COMMON", dirname(dirname(dirname(dirname(__FILE__)))) . "/");
if(!defined("CMS_APPLICATION_WEBAPP")) define("CMS_APPLICATION_WEBAPP", dirname(CMS_COMMON) . "/app/webapp/");

// app/webapp/以下にbaseディレクトリがある場合は削除
if(file_exists(CMS_APPLICATION_WEBAPP . "base/")){
	$files = soy2_scanfiles(CMS_APPLICATION_WEBAPP . "base/");
	if(count($files)){
		foreach($files as $file){
			@unlink($file);
		}
	}
	if(count(soy2_scanfiles(CMS_APPLICATION_WEBAPP . "base/")) === 0){
		rmdir(CMS_APPLICATION_WEBAPP . "base/");
	}
}