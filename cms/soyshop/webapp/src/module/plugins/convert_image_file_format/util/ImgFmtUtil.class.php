<?php

class ImgFmtUtil{

	const ON = 1;
	const OFF = 0;

	const APP_TYPE_CART = 0;
	const APP_TYPE_MYPAGE = 1;

	const FMT_TYPE_EMPTY = "empty";
	const FMT_TYPE_WEBP = "webp";
	const FMT_TYPE_AVIF = "avif";

	public static function getPageDisplayConfig(){
		$cnf = SOYShop_DataSets::get("convert_image_file_format.config", null);
		if(!is_null($cnf)) return $cnf;

		$pageIds = array_keys(soyshop_get_page_list());

		//
		$cnf = array();
		foreach($pageIds as $pageId){
			$cnf[$pageId] = self::OFF;
		}

		return $cnf;
	}

	public static function savePageDisplayConfig(array $values){

		$pageIds = array_keys(soyshop_get_page_list());

		$cnf = array();
		foreach($pageIds as $pageId){
			$cnf[$pageId] = (in_array($pageId, $values)) ? self::ON : self::OFF;
		}

		SOYShop_DataSets::put("convert_image_file_format.config", $cnf);
	}

	/**
	 * @param int
	 * @return int
	 */
	public static function getAppPageDisplayConfig(int $mode=self::APP_TYPE_CART){
		return (int)SOYShop_DataSets::get("convert_image_file_format.".(string)$mode, self::OFF);
	}

	public static function saveAppPageDisplayConfig(int $mode=self::APP_TYPE_CART, int $on=self::OFF){
		SOYShop_DataSets::put("convert_image_file_format.".(string)$mode, $on);
	}

	/**
	 * @return string
	 */
	public static function getImageFormat(){
		switch(SOYShop_DataSets::get("convert_image_file_format.format", self::FMT_TYPE_EMPTY)){
			case self::FMT_TYPE_WEBP:
				return (function_exists("imagewebp")) ? self::FMT_TYPE_WEBP : self::FMT_TYPE_EMPTY;
			case self::FMT_TYPE_AVIF:
				return (function_exists("imageavif")) ? self::FMT_TYPE_AVIF : self::FMT_TYPE_EMPTY;
			default:
				return self::FMT_TYPE_EMPTY;
		}
	}

	/**
	 * @param string
	 */
	public static function saveImageFormat(string $fmt=self::FMT_TYPE_EMPTY){
		SOYShop_DataSets::put("convert_image_file_format.format", $fmt);
	}
}
