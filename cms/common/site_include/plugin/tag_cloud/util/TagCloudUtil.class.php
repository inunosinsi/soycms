<?php

class TagCloudUtil {

	public static function getConfig(){
		SOY2::import("domain.cms.DataSets");
		return DataSets::get("tag_cloud.config", array(
			"divide" => 10
		));
	}

	public static function saveConfig($values){
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
}
