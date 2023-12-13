<?php

class TagCloudBeforeOutput extends SOYShopSiteBeforeOutputAction{

    function beforeOutput(WebPage $page){
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
				$obj = SOY2DAOFactory::create("SOYShop_TagCloudDictionaryDAO")->getById($wordId);
			}else{	//ハッシュ値の場合
				$obj = SOY2DAOFactory::create("SOYShop_TagCloudDictionaryDAO")->getByHash($wordId);
			}
		}catch(Exception $e){
			$obj = new SOYShop_TagCloudDictionary();
		}

		if(!is_numeric($obj->getId())) return "";

		if(!defined("SOYSHOP_PUBLISH_LANGUAGE") || SOYSHOP_PUBLISH_LANGUAGE == "jp") return (string)$obj->getWord();

		// 多言語化
		$res = SOY2Logic::createInstance("module.plugins.tag_cloud.logic.MultilingualLogic")->translateByWordId((int)$obj->getId());
		if(is_string($res)) return $res;
		
		return (string)$obj->getWord();
	}
}

SOYShopPlugin::extension("soyshop.site.beforeoutput", "tag_cloud", "TagCloudBeforeOutput");
