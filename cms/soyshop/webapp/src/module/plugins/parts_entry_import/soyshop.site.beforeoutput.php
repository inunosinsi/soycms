<?php

class EntryImportBeforeOutput extends SOYShopSiteBeforeOutputAction{

	function beforeOutput(WebPage $page){

		SOY2::import("util.SOYAppUtil");
		SOY2::import("module.plugins.parts_entry_import.component.EntryListComponent");
		SOY2::import("module.plugins.parts_entry_import.util.EntryImportUtil");

		$config = EntryImportUtil::getConfig();
		$old = SOYAppUtil::switchAdminDsn();

		SOY2::import("util.CMSPlugin");
		SOY2::import("util.UserInfoUtil");

		$siteId = (isset($config["siteId"])) ? $config["siteId"] : "";
		$site = EntryImportUtil::getSite($siteId);
		
		// 連携したショップの_SITE_ROOT_を定義しておく
		if(!defined("_SITE_ROOT_") && strlen($siteId)){
			$shopSiteRoot = dirname(SOYSHOP_SITE_DIRECTORY)."/".$siteId;
			if(file_exists($shopSiteRoot."/index.php")){
				define("_SITE_ROOT_", $shopSiteRoot);
			}
		}

		SOYAppUtil::resetAdminDsn($old);


		$old = EntryImportUtil::switchSiteDsn($site->getDataSourceName());

		/* サイト → ブログ → 記事一覧 */
		$blogId = (isset($config["blogId"])) ? (int)$config["blogId"] : 0;
		$count = (isset($config["count"])) ? (int)$config["count"] : 0;
		$page->createAdd("entry_list","EntryListComponent", array(
			"soy2prefix" => "block",
			"list" => ($count > 0) ? EntryImportUtil::getBlogEntiryList($blogId, $count) : array(),
			"blogUrl" => (is_numeric($blogId)) ? EntryImportUtil::getBlogUrl($blogId, $site->getUrl()) : "",
			"customFields" => (isset($siteId)) ? EntryImportUtil::getCustomfieldConfig($siteId) : array(),
			"thisIsNewDate" => (isset($siteId)) ? EntryImportUtil::getSOYCMSThisIsNewConfig($siteId) : 0,
			"thumbnailConfig" => (isset($siteId)) ? EntryImportUtil::getThumbnailPluginConfig($siteId) : null
		));

		//元に戻す
		EntryImportUtil::resetSiteDsn($old);
	}
}

SOYShopPlugin::extension("soyshop.site.beforeoutput","parts_entry_import","EntryImportBeforeOutput");
