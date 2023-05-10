<?php

class TagCloudUtil {

	public static function getConfig(){
		return self::_config();
	}

	private static function _config(){
		return SOYShop_DataSets::get("tag_cloud.config", array(
			"divide" => 10,
			"tags" => ""
		));
	}

	public static function saveConfig(array $values){
		if(isset($values["tags"]) && strlen($values["tags"])) $values["tags"] = self::_shapeTags($values["tags"]);
		SOYShop_DataSets::put("tag_cloud.config", $values);
	}

	public static function getDisplayCount(string $tmp){
		if(preg_match('/(<[^>]*[^\/]block:id=\"tag_cloud_word_list\"[^>]*>)/', $tmp, $tm)){
			if(preg_match('/cms:count=\"(.*?)\"/', $tm[1], $t)){
				if(isset($t[1]) && is_numeric($t[1])) return (int)$t[1];
			}
		}
		return null;
	}

	public static function isRandomMode(string $tmp){
		if(preg_match('/(<[^>]*[^\/]block:id=\"tag_cloud_word_list\"[^>]*>)/', $tmp, $tm)){
			if(preg_match('/cms:random=\"(.*?)\"/', $tm[1], $t)){
				if(isset($t[1]) && $t[1] = "on") return true;
			}
		}
		return false;
	}

	public static function getRank(int $i){
		static $div, $rank;
		if(is_null($rank)) $rank = 0;
		if(is_null($div)){
			$cnf = self::_config();
			$div = (isset($cnf["divide"]) && (int)$cnf["divide"]) ? (int)$cnf["divide"] : 1;
		}
		if($i % $div === 0) $rank++;
		return $rank;
	}

	private static function _shapeTags(string $tagsChain){
		$tagsChain = trim($tagsChain);
		if(!strlen($tagsChain)) return "";
		$tagsChain = trim(str_replace("、", ",", $tagsChain));

		$tags = explode(",", $tagsChain);
		$list = array();
		foreach($tags as $tag){
			$tag = trim($tag);
			if(!strlen($tag)) continue;
			$list[] = $tag;
		}
		return implode(",", $list);
	}

	public static function getRegisterdTagsByItemId(int $itemId){
		static $tags;
		if(!is_numeric($itemId)) return array();
		if(is_null($tags)) $tags = array();
		if(isset($tags[$itemId])) return $tags[$itemId];

		$tags[$itemId] = array();

		try{
			$links = self::_linkDao()->getByItemId($itemId);
		}catch(Exception $e){
			$links = array();
		}

		if(!count($links)) return array();

		foreach($links as $link){
			try{
				$obj = self::_dicDao()->getById($link->getWordId());
				$tags[$itemId][$link->getWordId()] = array("word" => $obj->getWord(), "hash" => $obj->getHash());
			}catch(Exception $e){
				//
			}
		}

		return $tags[$itemId];
	}

	public static function setHash(){
		$wordIds = self::_dicDao()->getNoHashWordIds();
		if(!count($wordIds)) return "";

		foreach($wordIds as $id){
			try{
				//下記二行でhashの自動生成
				$obj = self::_dicDao()->getById($id);
				self::_dicDao()->update($obj);
			}catch(Exception $e){
				//
			}
		}
	}

	public static function generateHash(string $str){
		return substr(md5($str), 0, 16);
	}

	private static function _linkDao(){
		static $dao;
		if(is_null($dao)) {
			SOY2::import("module.plugins.tag_cloud.domain.SOYShop_TagCloudLinkingDAO");
			$dao = SOY2DAOFactory::create("SOYShop_TagCloudLinkingDAO");
		}
		return $dao;
	}

	private static function _dicDao(){
		static $dao;
		if(is_null($dao)){
			SOY2::import("module.plugins.tag_cloud.domain.SOYShop_TagCloudDictionaryDAO");
			$dao = SOY2DAOFactory::create("SOYShop_TagCloudDictionaryDAO");
		}
		return $dao;
	}

	public static function getPageUrlSettedTagCloud(){
		$pageId = self::_getPageIdSettedTagCloud();
		return (is_numeric($pageId)) ? soyshop_get_page_url(soyshop_get_page_object($pageId)->getUri()) : "";
	}

	public static function getPageIdSettedTagCloud(){
		return self::_getPageIdSettedTagCloud();
	}

	private static function _getPageIdSettedTagCloud(){
		static $pageId;
		if(is_numeric($pageId)) return $pageId;

		try{
			$pages = soyshop_get_hash_table_dao("page")->getByType(SOYShop_Page::TYPE_LIST);
		}catch(Exception $e){
			$pages = array();
		}
		if(!count($pages)) return 0;

		$pageId = 0;
		foreach($pages as $page){
			if($pageId > 0) continue;
			if(is_null($page->getObject())) continue;
			if($page->getObject()->getType() != "custom") continue;
			if($page->getObject()->getModuleId() == "tag_cloud" && $page->getId() > 0) $pageId = (int)$page->getId();
		}
		unset($pages);

		return $pageId;
	}

	public static function getTagCloudAlias(){
		$args = soyshop_get_arguments();
		if(!isset($args[0])) return "";

		//第一引数の値がタグクラウドのワードであるか？を確認する
		$tagObj = self::_getWordObjectByAlias($args[0]);
		if(!is_numeric($tagObj->getId())) return "";

		if(!defined("SOYSHOP_PUBLISH_LANGUAGE") || SOYSHOP_PUBLISH_LANGUAGE == "jp") return (string)$tagObj->getWord();

		// 多言語対応
		$res = SOY2Logic::createInstance("module.plugins.tag_cloud.logic.MultilingualLogic")->translateByWordId((int)$tagObj->getId());
		if(is_string($res)) return $res;

		return $tagObj->getWord();
	}

	public static function getWordIdByAlias(string $tag){
		return (string)self::_getWordObjectByAlias($tag)->getId();
	}

	private static function _getWordObjectByAlias(string $tag){
		$dao = self::_dao();
		try{
			return $dao->getByHash($tag);
		}catch(Exception $e){
			try{
				return $dao->getByWord($tag);
			}catch(Exception $e){
				try{
					return $dao->getByWord(rawurldecode($tag));
				}catch(Exception $e){
					try{
						return $dao->getById($tag);
					}catch(Exception $e){
						//
					}
				}
			}
		}

		// 多言語化対応
		if((defined("SOYSHOP_PUBLISH_LANGUAGE") && SOYSHOP_PUBLISH_LANGUAGE != "jp")){
			// 応急処置
			if(is_numeric(strpos($tag, "%20"))) $tag = str_replace("%20", " ", $tag);
			SOY2::import("module.plugins.tag_cloud.domain.SOYShop_TagCloudLanguageDAO");
			try{
				 $wordId = SOY2DAOFactory::create("SOYShop_TagCloudLanguageDAO")->getByLangAndLabel(SOYSHOP_PUBLISH_LANGUAGE, $tag)->getWordId();
				 return $dao->getById($wordId);
			}catch(Exception $e){
				//
			}
		}

		return new SOYShop_TagCloudDictionary();
	}

	// soyshop_tag_cloud_dictionaryに格納していないタグも格納しておく
	public static function prepareCategory(array $categoryList){
		$cnf = self::_config();
		$tags = explode(",", $cnf["tags"]);
		if(!count($tags)) return;

		SOY2::import("module.plugins.tag_cloud.domain.SOYShop_TagCloudDictionaryDAO");
		$dicDao = SOY2DAOFactory::create("SOYShop_TagCloudDictionaryDAO");
		foreach($tags as $tag){
			$tag = trim($tag);
			try{
				$tagObj = $dicDao->getByWord($tag);
			}catch(Exception $e){
				$tagObj = new SOYShop_TagCloudDictionary();
				$tagObj->setWord($tag);
				try{
					$dicDao->insert($tagObj);
				}catch(Exception $e){
					//
				}
			}
		}

		$categoryIds = array_keys($categoryList);
		try{
			$res = $dicDao->executeQuery("SELECT id FROM soyshop_tag_cloud_dictionary WHERE category_id NOT IN (" . implode(",", $categoryIds) . ")");
		}catch(Exception $e){
			$res = array();
		}
		if(!count($res)) return;

		//該当するタグは未分類にする
		$wordIds = array();
		foreach($res as $v){
			$wordIds[] = (int)$v["id"];
		}
		try{
			$dicDao->executeUpdateQuery("UPDATE soyshop_tag_cloud_dictionary SET category_id = 0 WHERE id IN (" . implode(",", $wordIds) . ")");
		}catch(Exception $e){
			//
		}
	}

	public static function getTagCategoryList(){
		static $list;
		if(is_null($list)){
			SOY2::import("module.plugins.tag_cloud.domain.SOYShop_TagCloudCategoryDAO");
			try{
				$categories = SOY2DAOFactory::create("SOYShop_TagCloudCategoryDAO")->get();
			}catch(Exception $e){
				$categories = array();
			}
			if(!count($categories)) return array();
			
			$list = array();
			foreach($categories as $cat){
				$list[(int)$cat->getId()] = $cat->getLabel();
			}
			$list[0] = "未分類";
		}
		return $list;
	}

	private static function _dao(){
		static $dao;
		if(is_null($dao)) {
			SOY2::import("module.plugins.tag_cloud.domain.SOYShop_TagCloudDictionaryDAO");
			$dao = SOY2DAOFactory::create("SOYShop_TagCloudDictionaryDAO");
		}
		return $dao;
	}
}
