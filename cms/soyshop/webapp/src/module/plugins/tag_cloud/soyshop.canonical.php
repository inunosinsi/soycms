<?php
/*
 */
class TagCloudCanonical extends SOYShopCanonicalBase{

	function canonical(){
		SOY2::import("module.plugins.tag_cloud.util.TagCloudUtil");
		return (SOYSHOP_PAGE_ID == TagCloudUtil::getPageIdSettedTagCloud()) ? TagCloudUtil::getTagCloudAlias() : null;
	}
}
SOYShopPlugin::extension("soyshop.canonical", "tag_cloud", "TagCloudCanonical");
