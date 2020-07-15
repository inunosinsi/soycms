<?php

class HTMLCachePageUpdate extends SOYShopPageUpdate{

	function onUpdate($pageId){
		SOY2::import("module.plugins.x_html_cache.util.HTMLCacheUtil");
		HTMLCacheUtil::removeCacheFiles();
	}
}

SOYShopPlugin::extension("soyshop.page.update", "x_html_cache", "HTMLCachePageUpdate");
