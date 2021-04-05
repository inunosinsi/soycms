<?php

class AffiliateA8flyOnOutput extends SOYShopSiteOnOutputAction{

	/**
	 * @return string
	 */
	function onOutput($html){
		//XHTMLではないXMLでは出力しない
		if(
			strpos($html, '<?xml version="1"') !== false
			&&
			strpos($html, '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML') === false
		){
			return $html;
		}

		//RSS, Atomでは出力しない
		if(
			strpos($html, '<rss version="2.0">') !== false
			||
			strpos($html, '<feed xml:lang="ja" xmlns="http://www.w3.org/2005/Atom">') !== false
		){
			return $html;
		}

		//マイページでは表示しない
		//if(defined("SOYSHOP_MYPAGE_MODE") && SOYSHOP_MYPAGE_MODE) return $html;

		//</head>の直前。なければ挿入しない
		if(stripos($html, '</head>') === false) return $html;

		SOY2::import("module.plugins.affiliate_a8fly.util.AffiliateA8flyUtil");
		$config = AffiliateA8flyUtil::getConfig();
		if(!isset($config["id"]) || !strlen($config["id"])) return $html;

		$tag = "<script src=\"//statics.a8.net/a8sales/a8sales.js\"></script>";

		//カートページではa8crossDomainを読み込まない
		if(!SOYSHOP_APPLICATION_MODE && !SOYSHOP_CART_MODE){
			$tag .= "\n<script src=\"//statics.a8.net/a8sales/a8crossDomain.js\"></script>";
		}

		return str_ireplace('</head>', $tag . "\n" . '</head>', $html);
	}
}

SOYShopPlugin::extension("soyshop.site.onoutput", "affiliate_a8fly", "AffiliateA8flyOnOutput");
