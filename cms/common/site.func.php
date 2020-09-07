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
		if(!is_dir($dir) && !file_exists($dir)) mkdir($dir);
		$hash = substr($hash, 1);
	}

	$filepath = $dir . $hash . ".html";
	if(file_exists($filepath)){
		echo file_get_contents($filepath);
		exit;
	}
}
