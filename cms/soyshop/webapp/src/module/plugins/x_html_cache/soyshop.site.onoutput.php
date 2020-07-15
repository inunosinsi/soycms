<?php

class HTMLCacheOnOutput extends SOYShopSiteOnOutputAction{

	/**
	 * @return string
	 */
	function onOutput($html){
		//GETがある場合は検索ページと見なして対象外とする
		if(isset($_GET["q"])) return $html;

		//GETの値がある場合は対象外
		if(isset($_SERVER["REDIRECT_QUERY_STRING"]) && strpos($_SERVER["REDIRECT_QUERY_STRING"], "pathinfo") != 0) return $html;

		SOY2::import("module.plugins.x_html_cache.util.HTMLCacheUtil");
		$cnf = HTMLCacheUtil::getPageDisplayConfig();
		if(!isset($cnf[SOYSHOP_PAGE_ID]) || $cnf[SOYSHOP_PAGE_ID] != 1) return $html;

		//404ページの場合は静的化しない
		if(soyshop_get_page_object(SOYSHOP_PAGE_ID)->getUri() == SOYSHOP_404_PAGE_MARKER) return $html;

		//ページの種類に関係なくHTMLキャッシュを生成
		HTMLCacheUtil::generateStaticHTMLCacheFile($html);

		return $html;
	}
}

SOYShopPlugin::extension("soyshop.site.onoutput", "x_html_cache", "HTMLCacheOnOutput");
