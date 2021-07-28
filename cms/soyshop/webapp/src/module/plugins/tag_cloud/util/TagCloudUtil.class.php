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

	public static function saveConfig($values){
		if(isset($values["tags"]) && strlen($values["tags"])) $values["tags"] = self::_shapeTags($values["tags"]);
		SOYShop_DataSets::put("tag_cloud.config", $values);
	}

	public static function getDisplayCount($tmp){
		if(preg_match('/(<[^>]*[^\/]p_block:id=\"tag_cloud_word_list\"[^>]*>)/', $tmp, $tm)){
			if(preg_match('/cms:count=\"(.*?)\"/', $tm[1], $t)){
				if(isset($t[1]) && is_numeric($t[1])) return (int)$t[1];
			}
		}
		return null;
	}

	public static function isRandomMode($tmp){
		if(preg_match('/(<[^>]*[^\/]p_block:id=\"tag_cloud_word_list\"[^>]*>)/', $tmp, $tm)){
			if(preg_match('/cms:random=\"(.*?)\"/', $tm[1], $t)){
				if(isset($t[1]) && $t[1] = "on") return true;
			}
		}
		return false;
	}

	public static function getRank($i){
		static $div, $rank;
		if(is_null($rank)) $rank = 0;
		if(is_null($div)){
			$cnf = self::_config();
			$div = (isset($cnf["divide"]) && (int)$cnf["divide"]) ? (int)$cnf["divide"] : 1;
		}
		if($i % $div === 0) $rank++;
		return $rank;
	}

	private static function _shapeTags($tags){
		$tags = trim($tags);
		if(!strlen($tags)) return "";
		$tags = trim(str_replace("、", ",", $tags));

		$tagsArray = explode(",", $tags);
		$list = array();
		foreach($tagsArray as $tag){
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

	public static function generateHash($str){
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
			$pages = SOY2DAOFactory::create("site.SOYShop_PageDAO")->getByType(SOYShop_Page::TYPE_LIST);
		}catch(Exception $e){
			$pages = array();
		}
		if(!count($pages)) return 0;

		$pageId = 0;
		foreach($pages as $page){
			if($page->getObject()->getType() != "custom") continue;
			if($page->getObject()->getModuleId() == "tag_cloud") $pageId = $page->getId();
		}
		unset($pages);

		return $pageId;
	}

	public static function getTagCloudAlias(){
		$args = soyshop_get_arguments();
		if(!isset($args[0])) return null;

		//第一引数の値がタグクラウドのワードであるか？を確認する
		$tagObj = self::_getWordObjectByAlias($args[0]);
		return (is_numeric($tagObj->getId())) ? $tagObj->getWord() : null;
	}

	public static function getWordIdByAlias(string $tag){
		return self::_getWordObjectByAlias($tag)->getId();
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
						return new SOYShop_TagCloudDictionary();
					}
				}
			}
		}
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
