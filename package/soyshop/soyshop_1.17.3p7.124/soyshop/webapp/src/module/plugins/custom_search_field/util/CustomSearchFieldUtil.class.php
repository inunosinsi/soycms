<?php

class CustomSearchFieldUtil{
	
	const PLUGIN_PREFIX = "csf";	//csf:id="***"

	const TYPE_STRING = "string";
	const TYPE_TEXTAREA = "textarea";
	const TYPE_RICHTEXT = "richtext";
	const TYPE_INTEGER = "integer";
	const TYPE_RANGE = "range";
	const TYPE_CHECKBOX = "checkbox";
	const TYPE_RADIO = "radio";
	const TYPE_SELECT = "select";
	
	public static function getConfig(){
		return SOYShop_DataSets::get("custom_search.config", array());
	}
	
	public static function saveConfig($values){
		return SOYShop_DataSets::put("custom_search.config", $values);
	}

	public static function getSearchConfig(){
		return SOYShop_DataSets::get("custom_search.search_config", array(
			"search" => array(
				"single" => 1,
				"parent" => 1,
				"child" => 0,
				"download" => 1
			)
		));
	}
	
	public static function saveSearchConfig($values){
		foreach(array("single", "parent", "child", "download") as $t){
			$values["search"][$t] = (isset($values["search"][$t])) ? (int)$values["search"][$t] : 0;
		}
		return SOYShop_DataSets::put("custom_search.search_config", $values);
	}
	
	public static function getTypeList(){
		return array(
			self::TYPE_STRING => "文字列",
			self::TYPE_TEXTAREA => "複数行文字列",
			self::TYPE_RICHTEXT => "リッチテキスト",
			self::TYPE_INTEGER => "数字",
			self::TYPE_RANGE => "数字(範囲)",
			self::TYPE_CHECKBOX => "チェックボックス",
			self::TYPE_RADIO => "ラジオボタン",
			self::TYPE_SELECT => "セレクトボックス"
		);
	}
	
	public static function getTypeText($key){
		$list = self::getTypeList();
		return (isset($list[$key])) ? $list[$key] : "";
	}
	
	public static function checkIsType($type){
		$list = self::getTypeList();
		return (isset($list[$type]));
	}
	
	public static function getIsOpenCategoryList(){
		try{
			$categories = SOY2DAOFactory::create("shop.SOYShop_CategoryDAO")->getByIsOpen(1);
		}catch(Exception $e){
			return array();
		}
		
		if(!count($categories)) return array();
		
		$list = array();
		
		foreach($categories as $category){
			$list[$category->getId()] = $category->getName();
		}
		
		return $list;
	}
}
?>