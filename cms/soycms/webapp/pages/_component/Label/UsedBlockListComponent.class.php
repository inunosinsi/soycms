<?php

class UsedBlockListComponent extends HTMLList{

	function populateItem($entity, $pageId){
		$cmpName = (isset($entity["type"])) ? $entity["type"] : "";
		$this->addLabel("page_title", array(
			"text" => (strpos($cmpName, "SiteLabel") === false && is_numeric($pageId)) ? self::_getPageTitleById($pageId) : "---"
		));

		$this->addLabel("block_soy_id", array(
			"text" => (isset($entity["soy"]) && is_string($entity["soy"]) && strlen($entity["soy"])) ? "block:id=\"" . $entity["soy"] . "\"" : ""
		));

		$this->addLabel("block_name", array(
			"text" => self::_getBlockName($cmpName)
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

	private function _getBlockName($name){
		if(!strlen($name)) return "---";
		if(strpos($name, "Labeled") === 0){
			return "ラベルブロック";
		}else if(strpos($name, "Site") === 0){
			return "他サイトブロック";
		}else if(strpos($name, "Multi") === 0){
			return "ブログリンクブロック";
		}else{
			return "";
		}
	}
}
