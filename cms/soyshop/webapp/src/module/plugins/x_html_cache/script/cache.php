<?php
function static_cache_execute(){
	//GETパラメータがある場合、読み込まないものがある
	if(isset($_GET["captcha"]) || isset($_GET["language"])) return;

	$pathInfo = (isset($_SERVER["PATH_INFO"])) ? $_SERVER["PATH_INFO"] : "_top";
	$alias = trim(substr($pathInfo, strrpos($pathInfo, "/")), "/");

	//soyshop/webapp/conf/shop/***.conf.phpよりも後のこのファイルが読まれている必要がある
	if(defined("SOYSHOP_SITE_DIRECTORY")){
		$dir = SOYSHOP_SITE_DIRECTORY . ".cache/static_cache/";

		if(!file_exists($dir)) mkdir($dir);

		$dir .= (is_numeric($alias)) ? "n/" : "s/";
		if(!file_exists($dir)) mkdir($dir);

		$hash = md5($pathInfo);
		for($i = 0; $i < 10; ++$i){
			$dir .= substr($hash, 0, 1) . "/";
			if(!file_exists($dir)) mkdir($dir);
			$hash = substr($hash, 1);
		}

		$filepath = $dir . $hash . ".html";
		if(file_exists($filepath)){
			echo file_get_contents($filepath);
			exit;
		}
	}
}
static_cache_execute();
