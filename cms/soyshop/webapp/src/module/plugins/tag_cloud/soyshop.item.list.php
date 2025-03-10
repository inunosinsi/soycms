<?php
class TagCloudItemList extends SOYShopItemListBase{

	const FIELD_ID = "tag_cloud";

	/**
	 * @return string
	 */
	function getLabel(){
		return "TagCloudItemList";
	}

	/**
	 * @return array
	 */
	function getItems($pageObj, $offset, $limit){
		$wordId = self::_getWordIdFromParam();
		if(is_null($wordId) || !is_numeric($wordId)) return array();

		return self::_logic()->search($wordId, $limit);
	}

	/**
	 * @return number
	 */
	function getTotal($pageObj){
		$wordId = self::_getWordIdFromParam();
		if(is_null($wordId) || !is_numeric($wordId)) return 0;
		return self::_logic()->getTotal($wordId);
	}

	private function _getWordIdFromParam(){
		$args = soyshop_get_arguments();
		if(!isset($args[0])) return null;

		SOY2::import("module.plugins.tag_cloud.util.TagCloudUtil");
		return TagCloudUtil::getWordIdByAlias($args[0]);
	}

	private function _logic(){
		static $logic;
		if(is_null($logic)) $logic = SOY2Logic::createInstance("module.plugins.tag_cloud.logic.TagCloudBlockItemLogic");
		return $logic;
	}
}

SOYShopPlugin::extension("soyshop.item.list", "tag_cloud", "TagCloudItemList");
