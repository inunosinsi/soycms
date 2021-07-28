<?php
class CustomSearchFieldItemList extends SOYShopItemListBase{

	const FIELD_ID = "custom_search_field";

	/**
	 * @return string
	 */
	function getLabel(){
		return "CustomSearchFieldItemList";
	}

	/**
	 * @return array
	 */
	function getItems($pageObj, $offset, $limit){
		list($key, $value) = self::_getKeyAndValue();
		return self::_logic()->getItemList($pageObj, $key, $value, self::_current(), $offset, (int)$limit);
	}

	/**
	 * @return number
	 */
	function getTotal($pageObj){
		list($key, $value) = self::_getKeyAndValue();
		return self::_logic()->countItemList($key, $value);
	}

	private function _getKeyAndValue(){
		$args = soyshop_get_arguments();
		return (count($args) > 1) ? array($args[0], $args[1]) : array(null, null);
		return array("", "");
	}

	private function _current(){
		$args = soyshop_get_arguments();
		if(count($args) < 3) return 1;
		if(preg_match('/page-(.*)\.html/', $args[2], $tmp)){
			if(isset($tmp[1]) && is_numeric($tmp[1])) return (int)$tmp[1];
		}
		return 1;
	}

	private function _logic(){
		static $logic;
		if(is_null($logic)) $logic = SOY2Logic::createInstance("module.plugins.custom_search_field.logic.SearchLogic");
		return $logic;
	}
}

SOYShopPlugin::extension("soyshop.item.list", "custom_search_field", "CustomSearchFieldItemList");
