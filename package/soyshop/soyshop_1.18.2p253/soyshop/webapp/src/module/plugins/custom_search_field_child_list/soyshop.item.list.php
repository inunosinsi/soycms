<?php
class CustomSearchFieldChildList extends SOYShopItemListBase{

	const FIELD_ID = "custom_search_field_child_list";

	/**
	 * @return string
	 */
	function getLabel(){
		return "CustomSearchFieldChildList";
	}
	
	/**
	 * @return array
	 */
	function getItems($pageObj, $offset, $limit){
		if(!self::checkConfig()) return array();
	
		self::prepare();
		list($key, $value) = self::getKeyAndValue($pageObj->getPage()->getUri());
		return $this->searchLogic->getItemList($pageObj, $key, $value, self::getCurrent(), $offset, (int)$limit);
	}
	
	/**
	 * @return number
	 */
	function getTotal($pageObj){
		if(!self::checkConfig()) return 0;
		
		self::prepare();
		list($key, $value) = self::getKeyAndValue($pageObj->getPage()->getUri());
		return $this->searchLogic->countItemList($key, $value);
	}
	
	private function getKeyAndValue($uri){
		$values = str_replace($uri, "", substr($_SERVER["PATH_INFO"], 1));
		if(strpos($values, "/") === 0) $values = substr($values, 1);
		
		$array = explode("/", $values);
		return array($array[0], $array[1]);
	}
	
	private function getCurrent(){
		$page = substr($_SERVER["REQUEST_URI"], strrpos($_SERVER["REQUEST_URI"], "/") + 1);
		if(preg_match('/page-(.*)\.html/', $page, $tmp)){
			if(isset($tmp[1]) && is_numeric($tmp[1])) return (int)$tmp[1];
		}
		return 1;
	}
	
	private function checkConfig(){
		SOY2::import("module.plugins.custom_search_field_child_list.util.CustomSearchChildListUtil");
		
		//カスタムサーチフィールドがインストールされているか？
		if(!CustomSearchChildListUtil::checkInstalledCustomSearchField()) return false;
		
		//商品詳細ページで子商品が表示できる設定になっているか？
		if(!CustomSearchChildListUtil::checkDisplayChildItemConfig()) return false;
		
		return true;
	}
	
	private function prepare(){
		if(!$this->searchLogic) $this->searchLogic = SOY2Logic::createInstance("module.plugins.custom_search_field_child_list.logic.ChildItemLogic");
	}
}

SOYShopPlugin::extension("soyshop.item.list", "custom_search_field_child_list", "CustomSearchFieldChildList");
