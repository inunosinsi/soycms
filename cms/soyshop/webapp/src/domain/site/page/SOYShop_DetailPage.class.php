<?php
class SOYShop_DetailPage extends SOYShop_PageBase{

	const ITEM_ORDER_ASC = 0;
	const ITEM_ORDER_DESC = 1;

	private $currentItem;
	private $sortOrder = self::ITEM_ORDER_ASC;
	private $sortType = "id";

	function getTitleFormatDescription(){
    	$html = array();

    	$html[] = "商品名:%ITEM_NAME%";

    	return implode("<br />", $html);
    }
    
    function getKeywordFormatDescription(){
    	$html = array();
    	$html[] = "ショップ名:%SHOP_NAME%";
    	$html[] = "商品名:%ITEM_NAME%";
    	return implode("<br />", $html);
    }
    
    function getDescriptionFormatDescription(){
    	$html = array();
    	$html[] = "ショップ名:%SHOP_NAME%";
    	$html[] = "商品名:%ITEM_NAME%";
    	return implode("<br />", $html);
    }

    function convertPageTitle($title){
    	if($this->currentItem){
    		return str_replace("%ITEM_NAME%", $this->currentItem->getName(), $title);
    	}
    	return $title;
    }

    function getCurrentItem() {
    	return $this->currentItem;
    }
    function setCurrentItem($currentItem) {
    	$this->currentItem = $currentItem;
    }
    
    function getSortOrder() {
		return $this->sortOrder;
	}
	function setSortOrder($sortOrder) {
		$this->sortOrder = $sortOrder;
	}
	
	function getSortType() {
		return $this->sortType;
	}
	function setSortType($sortType) {
		$this->sortType = $sortType;
	}
}
?>