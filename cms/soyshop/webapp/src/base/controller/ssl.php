<?php

/**
 * SSLチェック　httpsのリダイレクト設定に従って振り分け
 * @param String $uri
 * @param Array $args
 */
function check_ssl($uri, $args){
	switch(SOYShop_ShopConfig::load()->getSSLConfig()){
		case SOYShop_ShopConfig::SSL_CONFIG_HTTPS:
			redirect_to_ssl_url($uri, $args);
			break;
		//ログインチェック後にhttpsに飛ばす
		case SOYShop_ShopConfig::SSL_CONFIG_LOGIN:
			//ログイン
			if(MyPageLogic::getMyPage()->getIsLoggedin()){
				redirect_to_ssl_url($uri, $args);
			//ログアウト
			}else{
				redirect_to_non_ssl_url($uri, $args);
			}
			break;
		default:
		case SOYShop_ShopConfig::SSL_CONFIG_HTTP:
			redirect_to_non_ssl_url($uri, $args);
	}
}

/**
 * SSLチェック
 * ショップのURLがSSLを使う設定のときにhttpでアクセスされた場合はhttpsにリダイレクトする
 * @param String $uri
 * @param Array $args
 */
function redirect_to_ssl_url($uri, $args){
	if(!isset($_SERVER["HTTPS"])){
		if($uri != SOYSHOP_TOP_PAGE_MARKER) array_unshift($args, $uri);
		$uri = (is_array($args) && count($args)) ? implode("/", $args) : "";
		SOY2PageController::redirect(soyshop_get_ssl_site_url() . $uri, true);
		exit;
	}
}

/**
 * SSLチェック
 * ショップのURLがSSLを使わない設定のときにhttpsでアクセスされた場合はhttpにリダイレクトする
 * @param String $uri
 * @param Array $args
 */
function redirect_to_non_ssl_url($uri, $args){
	if(isset($_SERVER["HTTPS"])){
		if($uri != SOYSHOP_TOP_PAGE_MARKER) array_unshift($args, $uri);
		$uri = (is_array($args) && count($args)) ? implode("/", $args) : "";
		SOY2PageController::redirect(soyshop_get_site_url(true) . $uri, true);
		exit;
	}
}
