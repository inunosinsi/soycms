<?php
function soyshop_entry_list_import(string $html, HTMLPage $htmlObj){
	$obj = $htmlObj->create("soyshop_entry_list_import", "HTMLTemplatePage", array(
		"arguments" => array("soyshop_entry_list_import", $html)
	));

	SOY2::import("module.plugins.parts_entry_import.util.EntryImportUtil");

	$config = EntryImportUtil::getConfig();
	$siteId = (isset($config["siteId"])) ? $config["siteId"] : "";
	$blogId = (isset($config["blogId"])) ? (int)$config["blogId"] : 0;
	$count = (isset($config["count"])) ? (int)$config["count"] : 0;
	$siteUrl = "";
	$dsn = "";
	
	if(strlen($siteId) && $blogId > 0 && $count > 0){
		SOY2::import("util.SOYAppUtil");
		$old = SOYAppUtil::switchAdminDsn();
		SOY2::import("util.CMSPlugin");
		SOY2::import("util.UserInfoUtil");

		$site = EntryImportUtil::getSite($siteId);
		$dsn = $site->getDataSourceName();
		$siteUrl = $site->getUrl();
		
		// 連携したショップの_SITE_ROOT_を定義しておく
		if(!defined("_SITE_ROOT_") && strlen($siteId)){
			$shopSiteRoot = dirname(SOYSHOP_SITE_DIRECTORY)."/".$siteId;
			if(file_exists($shopSiteRoot."/index.php")){
				define("_SITE_ROOT_", $shopSiteRoot);
			}
		}

		SOYAppUtil::resetAdminDsn($old);
	}

	/* サイト → ブログ → 記事一覧 */
	SOY2::import("module.plugins.parts_entry_import.component.EntryListComponent");
	if(strlen($dsn)){
		$old = EntryImportUtil::switchSiteDsn($dsn);

		$obj->createAdd("entry_list", "EntryListComponent", array(
			"soy2prefix" => "block",
			"list" => EntryImportUtil::getBlogEntiryList($blogId, $count),
			"blogUrl" => EntryImportUtil::getBlogUrl($blogId, $siteUrl),
			"customFields" => EntryImportUtil::getCustomfieldConfig($siteId),
			"thisIsNewDate" => EntryImportUtil::getSOYCMSThisIsNewConfig($siteId),
			"thumbnailConfig" => EntryImportUtil::getThumbnailPluginConfig($siteId)
		));

		//元に戻す
		EntryImportUtil::resetSiteDsn($old);
	}else{	//dummy
		$obj->createAdd("entry_list", "HTMLList", array(
			"soy2prefix" => "block",
			"list" => array()
		));
	}

	$obj->display();
}