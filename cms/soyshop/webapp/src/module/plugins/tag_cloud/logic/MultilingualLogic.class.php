<?php

class MultilingualLogic extends SOY2LogicBase {

	function __construct(){
		if(!defined("SOYSHOP_PUBLISH_LANGUAGE")) define("SOYSHOP_PUBLISH_LANGUAGE", "jp");
		SOY2::import("module.plugins.tag_cloud.domain.SOYShop_TagCloudLanguageDAO");
	}

	/**
	 * @param array
	 * @return array
	 */
	function translate(array $words){
		if(!count($words)) return array();

		$ids = array();
		foreach($words as $word){
			$ids[] = (int)$word["word_id"];
		}
		
		$list = SOY2DAOFactory::create("SOYShop_TagCloudLanguageDAO")->getTranslatedWordListByWordIdAndLang($ids, SOYSHOP_PUBLISH_LANGUAGE);
		if(!count($list)) return $words;

		$cnt = count($words);
		for($i = 0; $i < $cnt; $i++){
			if(!isset($list[$words[$i]["word_id"]])) continue;
			$words[$i]["word"] = $list[$words[$i]["word_id"]];
		}

		return $words;
	}

	/**
	 * @param array
	 * @return array
	 */
	function translateOnCustomField(array $tags){
		static $list;
		if(!count($tags)) return array();

		// まとめてすべて取得しておく
		if(is_null($list)){
			$l = SOY2DAOFactory::create("SOYShop_TagCloudLanguageDAO")->getTranslatedWordList();
			$list = (isset($l[SOYSHOP_PUBLISH_LANGUAGE])) ? $l[SOYSHOP_PUBLISH_LANGUAGE] : array();
		}

		foreach($tags as $wordId => $tag){
			if(!isset($list[$wordId])) continue;
			$tags[$wordId]["word"] = $list[$wordId];
		}
		
		return $tags;
	}

	/**
	 * @param int
	 * @return string|null
	 */
	function translateByWordId(int $wordId){
		try{
			return SOY2DAOFactory::create("SOYShop_TagCloudLanguageDAO")->get($wordId, SOYSHOP_PUBLISH_LANGUAGE)->getLabel();
		}catch(Exception $e){
			return null;
		}
	}
}