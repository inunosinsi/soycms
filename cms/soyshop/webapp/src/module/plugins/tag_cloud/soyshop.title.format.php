<?php

class TagCloudTitleFormat extends SOYShopTitleFormatBase{

	const FORMAT = "%TAG_CLOUD%";

	function titleFormatOnListPage(){
		preg_match('/\d.*/', $_SERVER["REQUEST_URI"], $tmp);
		if(!isset($tmp[0]) || !is_numeric($tmp[0])) return array();

		$pageObject = soyshop_get_page_object((int)$tmp[0])->getPageObject();
		if($pageObject->getType() != SOYShop_ListPage::TYPE_CUSTOM || $pageObject->getModuleId() != "tag_cloud") return array();

		return array(
			array(
				"label" => "タグクラウド",
				"format" => self::FORMAT
			)
		);
	}

	function convertOnListPage(string $title){
		if(soy2_strpos($title, self::FORMAT) < 0) return $title;

		SOY2::import("module.plugins.tag_cloud.util.TagCloudUtil");
		return str_replace(self::FORMAT, TagCloudUtil::getTagCloudAlias(), $title);
	}
}
SOYShopPlugin::extension("soyshop.title.format", "tag_cloud", "TagCloudTitleFormat");
