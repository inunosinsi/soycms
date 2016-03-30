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
		
		if(is_null($config)){
			
			$pages = self::getPages();
			
			//
			$config = array();

			foreach($pages as $page){
				$config[$page->getId()] = self::INSERT_TAG_DISPLAY;
			}
		}
		
		return $config;
	}
	
	public static function savePageDisplayConfig($array){
		
		$pages = self::getPages();
		
		$config = array();
		foreach($pages as $page){
			$pageId = $page->getId();
			$config[$pageId] = (in_array($pageId, $array)) ? self::INSERT_TAG_DISPLAY : self::INSERT_TAG_NOT_DISPLAY;
		}
		SOYShop_DataSets::put("button_social_page_config", $config);
	}
	
	private static function getPages(){
		$pageDao = SOY2DAOFactory::create("site.SOYShop_PageDAO");
		try{
			$pages = $pageDao->get();
		}catch(Exception $e){
			$pages = array();
		}
		return $pages;
	}
}
?>