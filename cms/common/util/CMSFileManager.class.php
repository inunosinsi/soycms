<?php

class CMSFileManager{

	public static function getAllowedMimeTypes(){
		$mimetypes = null;
		if(!defined("ELFINDER_MODE")) define("ELFINDER_MODE", false);
		if(file_exists(SOY2::RootDir() . "/config/upload.config.php")){
			include_once(SOY2::RootDir() . "/config/upload.config.php");
		}
		if(is_array($mimetypes)) return $mimetypes;		
		return array('image/x-ms-bmp', 'image/gif', 'image/jpeg', 'image/png', 'image/webp', 'image/x-icon', 'text/plain', "text/css", "application/pdf");
	}

	private function __construct(){}

	public static function upload(string $root, string $dir, array $upload){
		//拡張子チェック→mimetypeに変更
		$pathinfo = pathinfo($upload["name"]);
		if(!isset($pathinfo["extension"])){
			return "Wrong extention";
		}

		$filepath = $root . "/" . $dir . "/" . $upload["name"];
		$res = move_uploaded_file($upload["tmp_name"], $filepath);
		@chmod($filepath,0666);
		return (is_bool($res)) ? $res : false;
	}
}