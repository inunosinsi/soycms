<?php
/*
 *  Created on 2009/09/09 by okada
 *
 *  サイトルートのindex.phpから読み込まれる
 *
 * TODO
 * 絵文字を使う場合、キャリア別のキャッシュを作成する必要がある。
 */

/**
 * サイトのページの処理を行い、出力する
 * キャッシュは使わないし、作らない
 */
function execute_site_normal(){
	include_once("site_include/SOYCMSOutputContents.class.php");
	SOYCMSOutputContents::execute_normal();
}

/**
 * キャッシュを必ず使う
 * 1.3.5a以前のSOY CMSでexecute_site_cacheを呼び出している場合への互換性対策
 */
function execute_site_cache(){
	if(!defined("SOYCMS_USE_CACHE")) define("SOYCMS_USE_CACHE", true);
	execute_site();
}

function execute_site_static_cache(){
	//静的キャッシュ
	static_cache_execute();
	execute_site_cache();
}

/**
 * サイトのページの処理を行いつつ、キャッシュを作る
 * キャッシュがあればそれを出力する
 */
function execute_site(){
	// IPアドレスによるアクセス拒否 速度の節約の為にパスを直書き
	foreach(array("malicious", "blacklist") as $f){
		if(!file_exists(__DIR__."/config/ip/".$f)) continue;
		$ips = explode("\n", trim(file_get_contents(__DIR__."/config/ip/".$f)));
		if(!count($ips)) continue;

		foreach($ips as $ip){
			$ip = trim($ip);
			if($_SERVER["REMOTE_ADDR"] == $ip){
				http_response_code(403);
				echo "Access Denied.";
				exit;
			}

			//*印がある場合
			if(is_numeric(strpos($ip, ".*"))){
				$ip = substr($ip, 0, strpos($ip, ".*"));
				$res = strpos($_SERVER["REMOTE_ADDR"], $ip);
				if(is_numeric($res) && $res === 0){
					http_response_code(403);
					echo "Access Denied.";
					exit;
				}
			}
		}
	}

	// ユーザーエージェントによるアクセス制限
	foreach(array("useragent") as $f){
		if(!isset($_SERVER['HTTP_USER_AGENT']) || !file_exists(__DIR__."/config/ip/".$f)) continue;
		$agents = explode("\n", trim(file_get_contents(__DIR__."/config/ip/".$f)));
		if(!count($agents)) continue;

		$userAgent = strtolower($_SERVER['HTTP_USER_AGENT']);

		foreach($agents as $agent){
			$agent = trim($agent);
			if(!strlen($agent)) continue;

			if(is_numeric(strpos($userAgent, strtolower($agent)))){
				http_response_code(403);
				echo "Access Denied.";
				exit;
			}
		}
	}

	include_once("site_include/SOYCMSOutputContents.class.php");
	$obj = new SOYCMSOutputContents();
	$obj->execute();
}

function static_cache_execute(){
	//トレイリングスラッシュ対策で末尾のスラッシュを抜いておく
	$pathInfo = (isset($_SERVER["PATH_INFO"])) ? rtrim($_SERVER["PATH_INFO"], "/") : "_top";
	$alias = trim(substr($pathInfo, strrpos($pathInfo, "/")), "/");
	
	$dir = _SITE_ROOT_ . "/.cache/static_cache/";
	if(!file_exists($dir)) mkdir($dir);

	$dir .= (is_numeric($alias)) ? "n/" : "s/";
	if(!file_exists($dir)) mkdir($dir);

	$hash = md5($pathInfo);
	for($i = 0; $i < 10; ++$i){
		$dir .= substr($hash, 0, 1) . "/";
		if(!is_dir($dir) && !file_exists($dir)) @mkdir($dir);
		$hash = substr($hash, 1);
	}

	$filepath = $dir . $hash . ".html";
	if(file_exists($filepath)){
		echo file_get_contents($filepath);
		exit;
	}
}
