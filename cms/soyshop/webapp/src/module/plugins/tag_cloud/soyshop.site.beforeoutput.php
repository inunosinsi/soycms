<?php

class TagCloudBeforeOutput extends SOYShopSiteBeforeOutputAction{

    function beforeOutput($page){
		$wordId = self::_getWordIdFromParam();
		$tag = (strlen($wordId)) ? self::_getTagByWordId($wordId) : "";

		$page->addLabel("tag_cloud_tag", array(
			"soy2prefix" => "cms",
			"text" => $tag
		));
    }

	private function _getWordIdFromParam(){
		$args = soyshop_get_arguments();
		if(!isset($args[0])) return "";

		SOY2::import("module.plugins.tag_cloud.util.TagCloudUtil");
		return TagCloudUtil::getWordIdByAlias($args[0]);
	}

	/**
	 * @param string|int
	 * @return string
	 */
	private function _getTagByWordId($wordId){
		SOY2::import("module.plugins.tag_cloud.domain.SOYShop_TagCloudDictionaryDAO");
		try{
			if(is_numeric($wordId)){
				return SOY2DAOFactory::create("SOYShop_TagCloudDictionaryDAO")->getById($wordId)->getWord();
			}else{	//ハッシュ値の場合
				return SOY2DAOFactory::create("SOYShop_TagCloudDictionaryDAO")->getByHash($wordId)->getWord();
			}
		}catch(Exception $e){
			//
		}
		return null;
	}
}

SOYShopPlugin::extension("soyshop.site.beforeoutput", "tag_cloud", "TagCloudBeforeOutput");
