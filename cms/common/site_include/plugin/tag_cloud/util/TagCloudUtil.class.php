<?php

class TagCloudUtil {

	public static function getConfig(){
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
}
