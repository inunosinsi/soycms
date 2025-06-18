<?php
function soyshop_tag_cloud_category(string $html, HTMLPage $page){

	$obj = $page->create("tag_cloud_category", "HTMLTemplatePage", array(
		"arguments" => array("tag_cloud_category", $html)
	));

	$categoryIds = array();
	$randomMode = false;
	$cnt = 10;
	SOY2::import("util.SOYShopPluginUtil");
    if(SOYShopPluginUtil::checkIsActive("tag_cloud")){
		SOY2::import("module.plugins.tag_cloud.domain.SOYShop_TagCloudCategoryDAO");
		$categoryIds = SOY2DAOFactory::create("SOYShop_TagCloudCategoryDAO")->getCategoryIdList();
		$randomMode = TagCloudUtil::isRandomMode($html);
		$cnt = TagCloudUtil::getDisplayCount($html);
	}

	SOY2::import("module.plugins.tag_cloud.util.TagCloudUtil");
	SOY2::import("module.plugins.tag_cloud.component.TagCloudClassifiedWordListComponent");
	$obj->createAdd("tag_cloud_classified_word_list", "TagCloudClassifiedWordListComponent", array(
		"soy2prefix" => "block",
		"list" => $categoryIds,
		"randomMode" => $randomMode,
		"count" => $cnt
	));

	$obj->display();
}
