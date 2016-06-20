<?php

class AsyncCartButtonUtil{
	
	const INSERT_TAG_DISPLAY = 1;
	const INSERT_TAG_NOT_DISPLAY = 0;
	
	const PAGE_TYPE_CART = "cart";
	const PAGE_TYPE_MYPAGE = "mypage";
	
	public static function getPageDisplayConfig(){
		$config = SOYShop_DataSets::get("async_cart_button.page_config", null);
		
		if(is_null($config)){
			
			$pages = self::getPages();
			
			//
			$config = array();

			foreach($pages as $page){
				$config[$page->getId()] = self::INSERT_TAG_DISPLAY;
			}
			
			//カートとマイページ
			$config[self::PAGE_TYPE_CART] = self::INSERT_TAG_DISPLAY;
			$config[self::PAGE_TYPE_MYPAGE] = self::INSERT_TAG_DISPLAY;
		}
		
		return $config;
	}
	
	public static function savePageDisplayConfig($values){
		
		$pages = self::getPages();
		
		$config = array();
		foreach($pages as $page){
			$pageId = $page->getId();
			$config[$pageId] = (in_array($pageId, $values)) ? self::INSERT_TAG_DISPLAY : self::INSERT_TAG_NOT_DISPLAY;
		}
		//カートとマイページ
		$config[self::PAGE_TYPE_CART] = (in_array(self::PAGE_TYPE_CART, $values)) ? self::INSERT_TAG_DISPLAY : self::INSERT_TAG_NOT_DISPLAY;
		$config[self::PAGE_TYPE_MYPAGE] = (in_array(self::PAGE_TYPE_MYPAGE, $values)) ? self::INSERT_TAG_DISPLAY : self::INSERT_TAG_NOT_DISPLAY;
		
		SOYShop_DataSets::put("async_cart_button.page_config", $config);
	}
	
	private static function getPages(){
		try{
			return SOY2DAOFactory::create("site.SOYShop_PageDAO")->get();
		}catch(Exception $e){
			return array();
		}
	}
}
?>