<?php

class AsyncCartButtonUtil{

	const INSERT_TAG_DISPLAY = 1;
	const INSERT_TAG_NOT_DISPLAY = 0;

	const PAGE_TYPE_CART = "cart";
	const PAGE_TYPE_MYPAGE = "mypage";

	public static function getPageDisplayConfig(){
		$config = SOYShop_DataSets::get("async_cart_button.page_config", null);
		if(is_array($config)) return $config;

		$config = array();

		$pageIds = array_keys(soyshop_get_page_list());
		foreach($pageIds as $pageId){
			$config[$pageId] = self::INSERT_TAG_DISPLAY;
		}

		//カートとマイページ
		$config[self::PAGE_TYPE_CART] = self::INSERT_TAG_DISPLAY;
		$config[self::PAGE_TYPE_MYPAGE] = self::INSERT_TAG_DISPLAY;

		return $config;
	}

	public static function savePageDisplayConfig($values){

		$pageIds = array_keys(soyshop_get_page_list());

		$config = array();
		foreach($pageIds as $pageId){
			$config[$pageId] = (in_array($pageId, $values)) ? self::INSERT_TAG_DISPLAY : self::INSERT_TAG_NOT_DISPLAY;
		}
		//カートとマイページ
		$config[self::PAGE_TYPE_CART] = (in_array(self::PAGE_TYPE_CART, $values)) ? self::INSERT_TAG_DISPLAY : self::INSERT_TAG_NOT_DISPLAY;
		$config[self::PAGE_TYPE_MYPAGE] = (in_array(self::PAGE_TYPE_MYPAGE, $values)) ? self::INSERT_TAG_DISPLAY : self::INSERT_TAG_NOT_DISPLAY;

		SOYShop_DataSets::put("async_cart_button.page_config", $config);
	}
}
