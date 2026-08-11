<?php

class SOYShop_SearchPage extends SOYShop_PageBase{

	private $module;
	private $displayCount;
	private $getOption = 0;	//0 - normal 1 - redirect
	private $redirectTo = "";

	/* sort */
	private $defaultSort = "name";
	private $customSort = "";
	private $isReverse = false;

	function getModule() {
		return $this->module;
	}
	function setModule($module) {
		$this->module = $module;
	}
	function getDisplayCount() {
		return $this->displayCount;
	}
	function setDisplayCount($displayCount) {
		$this->displayCount = $displayCount;
	}
	function getGetOption() {
		return $this->getOption;
	}
	function setGetOption($getOption) {
		$this->getOption = $getOption;
	}
	function getRedirectTo() {
		return $this->redirectTo;
	}
	function setRedirectTo($redirectTo) {
		$this->redirectTo = $redirectTo;
	}

	function getDefaultSort() {
		return $this->defaultSort;
	}
	function setDefaultSort($defaultSort) {
		$this->defaultSort = $defaultSort;
	}
	function getCustomSort() {
		return $this->customSort;
	}
	function setCustomSort($customSort) {
		$this->customSort = $customSort;
	}
	function getIsReverse() {
		return $this->isReverse;
	}
	function setIsReverse($isReverse) {
		$this->isReverse = $isReverse;
	}

	function getTitleFormatDescription(){
		return self::_getCommonFormat();
    }

    function getKeywordFormatDescription(){
		return self::_getCommonFormat();
    }

    function getDescriptionFormatDescription(){
		return self::_getCommonFormat();
    }

	private function _getCommonFormat(){
		$tags = parent::getCommonFormat();

		$html = array();
		$html[] = "<table style=\"margin-top:5px;\">";
		foreach($tags as $tag){
			$html[] = "<tr><td>" . $tag["label"] . "：</td><td><strong>" . $tag["format"] . "</strong></td></tr>";
		}
		$html[] = "</table>";
    	return implode("\n", $html);
	}
}
