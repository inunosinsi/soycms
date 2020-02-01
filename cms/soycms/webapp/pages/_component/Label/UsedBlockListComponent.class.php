<?php

class UsedBlockListComponent extends HTMLList{

	function populateItem($entity, $pageId){
		$this->addLabel("page_title", array(
			"text" => (is_numeric($pageId)) ? self::_getPageTitleById($pageId) : ""
		));

		$this->addLabel("block_soy_id", array(
			"text" => (is_string($entity) && strlen($entity)) ? "block:id=\"" . $entity . "\"" : ""
		));
	}

	private function _getPageTitleById($pageId){
		static $pages, $logic;
		if(is_null($pages)) $pages = array();
		if(is_null($logic)) $logic = SOY2Logic::createInstance("logic.site.Page.PageLogic");

		if(isset($pages[$pageId])) return $pages[$pageId];

		$pages[$pageId] = $logic->getById($pageId)->getTitle();
		return $pages[$pageId];
	}
}
