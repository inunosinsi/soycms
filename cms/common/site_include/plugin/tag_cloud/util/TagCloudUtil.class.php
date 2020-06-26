<?php

class TagCloudUtil {

	public static function getConfig(){
		return self::_config();
	}

	private static function _config(){
		SOY2::import("domain.cms.DataSets");
		return DataSets::get("tag_cloud.config", array(
			"divide" => 10,
			"tags" => ""
		));
	}

	public static function saveConfig($values){
		if(isset($values["tags"]) && strlen($values["tags"])) $values["tags"] = self::_shapeTags($values["tags"]);
		SOY2::import("domain.cms.DataSets");
		DataSets::put("tag_cloud.config", $values);
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

	public static function getRegisterdTagsByEntryId($entryId){
		static $tags;
		if(!is_numeric($entryId)) return array();
		if(is_null($tags)) $tags = array();
		if(isset($tags[$entryId])) return $tags[$entryId];

		$tags[$entryId] = array();

		try{
			$links = self::_linkDao()->getByEntryId($entryId);
		}catch(Exception $e){
			$links = array();
		}

		if(!count($links)) return array();

		foreach($links as $link){
			try{
				$obj = self::_dicDao()->getById($link->getWordId());
				$tags[$entryId][$link->getWordId()] = array("word" => $obj->getWord(), "hash" => $obj->getHash());
			}catch(Exception $e){
				//
			}
		}

		return $tags[$entryId];
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
			SOY2::import("site_include.plugin.tag_cloud.domain.TagCloudLinkingDAO");
			$dao = SOY2DAOFactory::create("TagCloudLinkingDAO");
		}
		return $dao;
	}

	private static function _dicDao(){
		static $dao;
		if(is_null($dao)){
			SOY2::import("site_include.plugin.tag_cloud.domain.TagCloudDictionaryDAO");
			$dao = SOY2DAOFactory::create("TagCloudDictionaryDAO");
		}
		return $dao;
	}

	public static function getPageUrlSettedTagCloudBlock(){
		$pageId = self::_getPageIdSettedTagCloudBlock();
		return (is_numeric($pageId)) ? self::_getUrlByPageId($pageId) : "";
	}

	public static function getPageIdSettedTagCloudBlock(){
		return self::_getPageIdSettedTagCloudBlock();
	}

	private static function _getPageIdSettedTagCloudBlock(){
		$sql = "SELECT page_id, object FROM Block WHERE class = :blk";

		$dao = new SOY2DAO();
		try{
			$res = $dao->executeQuery($sql, array(":blk" => "PluginBlockComponent"));
		}catch(Exception $e){
			$res = array();
		}

		if(!count($res)) return null;

		$pageId = null;
		foreach($res as $v){
			if(strpos($v["object"], "TagCloud")){
				$pageId = (int)$v["page_id"];
				break;
			}
		}
		return $pageId;
	}

	public static function getUrlByPageId($pageId){
		return self::_getUrlByPageId($pageId);
	}

	private static function _getUrlByPageId($pageId){
		$url = SOY2DAOFactory::create("cms.SiteConfigDAO")->get()->getConfigValue("url");
		if(is_null($url)) $url = CMSPageController::createLink("", true);

		try{
			$uri = SOY2DAOFactory::create("cms.PageDAO")->getById($pageId)->getUri();
			$url .= $uri;
		}catch(Exception $e){
			//
		}
		return $url;
	}
}
