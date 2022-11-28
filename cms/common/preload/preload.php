<?php
/**
 * PHP7.4から追加されたopcache.preloadの設定ファイルになります
 * https://www.php.net/manual/ja/opcache.configuration.php#ini.opcache.preload
 * このファイルをpreload.phpにリネームして、php.iniのopcache.preloadにリネームしたファイルのパスを登録することで使用できます
 * commonディレクトリのコードをpreloadに登録してみます
 */

function scandir_r($dir){
	$list = scandir($dir);
	$results = array();
	foreach($list as $record){
		if(in_array($record, array(".", ".."))){
			continue;
		}
		$path = rtrim($dir, "/")."/".$record;
		if(is_file($path)){
			$results[] = $path;
		}
		else{
			if(is_dir($path)){
				$results = array_merge($results, scandir_r($path));
			}
		}
	}
	return $results;
}

//soycms
$tmp = scandir_r(dirname(dirname(__FILE__)));
foreach($tmp as $path){
	if(!strpos($path, ".php")) continue;	//PHPファイルでないものは除く
	if(strpos($path, "preload")) continue;	//preloadディレクトリのコードも除く
	//opcache_compile_file($path);
}
