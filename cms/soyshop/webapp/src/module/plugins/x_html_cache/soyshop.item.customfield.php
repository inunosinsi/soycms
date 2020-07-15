<?php
class HTMLCacheItemCustomField extends SOYShopItemCustomFieldBase{

	function doPost(SOYShop_Item $item){
		SOY2::import("module.plugins.x_html_cache.util.HTMLCacheUtil");
		HTMLCacheUtil::removeCacheFiles();
	}

	function onDelete($id){
		SOY2::import("module.plugins.x_html_cache.util.HTMLCacheUtil");
		HTMLCacheUtil::removeCacheFiles();
	}
}
SOYShopPlugin::extension("soyshop.item.customfield", "x_html_cache", "HTMLCacheItemCustomField");
