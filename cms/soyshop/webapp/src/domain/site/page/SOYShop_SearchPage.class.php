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
    	$html = array();

    	$html[] = "検索ワード:%SEARCH_WORD%";

    	return implode(" ", $html);
    }
    
    function getKeywordFormatDescription(){
    	$html = array();
    	$html[] = "ショップ名:%SHOP_NAME%";
    	$html[] = "検索ワード:%SEARCH_WORD%";
    	return implode("<br />", $html);
    }
    
    function getDescriptionFormatDescription(){
    	$html = array();
    	$html[] = "ショップ名:%SHOP_NAME%";
    	$html[] = "検索ワード:%SEARCH_WORD%";
    	return implode("<br />", $html);
    }

    function convertPageTitle($title){
    	$q = "";
    	if(isset($_GET["q"])){
    		$q = htmlspecialchars($_GET["q"], ENT_QUOTES, "UTF-8");
    	}else if(isset($_GET["c_search"]["item_name"])){
    		$q = htmlspecialchars($_GET["c_search"]["item_name"], ENT_QUOTES, "UTF-8");
    	}
    	return str_replace("%SEARCH_WORD%", $q, $title);
    }
}
?>