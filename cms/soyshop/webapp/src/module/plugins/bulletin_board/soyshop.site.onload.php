<?php

class BulletinBoardSiteOnLoad extends SOYShopSiteOnLoadAction{

	function onLoad($page){
		if(!defined("SOYSHOP_MYPAGE_MODE") || !SOYSHOP_MYPAGE_MODE || !method_exists($page, "getMyPageId")) return;	// ←このコードでマイページを開いている事がわかる

		// @ToDo mypageで表示を禁止するページ
		$uri = $_SERVER["REQUEST_URI"];
		if(strpos($uri, "/" . SOYSHOP_ID . "/" . soyshop_get_mypage_uri() . "/") !== 0) return;

		$uri = rtrim(str_replace("/" . SOYSHOP_ID . "/" . soyshop_get_mypage_uri() . "/", "", $uri), "/");
		if(!strlen($uri)) return;

		$args = explode("/", $uri);
		switch($args[0]){
			default:
				//何もしない
		}
	}
}

SOYShopPlugin::extension("soyshop.site.onload", "bulletin_board", "BulletinBoardSiteOnLoad");
