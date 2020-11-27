<?php

class UsedBlogLabelListComponent extends HTMLList{

	function populateItem($entity){
		$this->addLabel("page_title", array(
			"text" => (is_numeric($entity)) ? self::_getPageTitleById($entity) : ""
		));
	}

	private function _getPageTitleById($pageId){
		static $pages, $logic;
		if(is_null($pages)) $pages = array();
		if(is_null($logic)) $logic = SOY2Logic::createInstance("logic.site.Page.PageLogic");

		if(!is_numeric($pageId)) $pageId = 0;

		if(isset($pages[$pageId])) return $pages[$pageId];

		$pages[$pageId] = $logic->getById($pageId)->getTitle();
		return $pages[$pageId];
	}
}
