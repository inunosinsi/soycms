<?php

class ButtonSocialUtil{

	const INSERT_TAG_DISPLAY = 1;
	const INSERT_TAG_NOT_DISPLAY = 0;

	public static function getConfig(){
		return SOYShop_DataSets::get("button_social", array(
			"app_id" => "",
			"admins" => "",
			"image" => "",
			"check_key" => "",
		));
	}

	public static function saveConfig($values){
		SOYShop_DataSets::put("button_social", $values);
	}

	public static function getPageDisplayConfig(){
		$config = SOYShop_DataSets::get("button_social_page_config", null);
		if(is_array($config)) return $config;

		$config = array();

		$pageIds = array_keys(soyshop_get_page_list());
		foreach($pageIds as $pageId){
			$config[$pageId] = self::INSERT_TAG_DISPLAY;
		}

		return $config;
	}

	public static function savePageDisplayConfig($array){
		$pageIds = array_keys(soyshop_get_page_list());

		$config = array();
		foreach($pageIds as $pageId){
			$config[$pageId] = (in_array($pageId, $array)) ? self::INSERT_TAG_DISPLAY : self::INSERT_TAG_NOT_DISPLAY;
		}
		SOYShop_DataSets::put("button_social_page_config", $config);
	}
}
