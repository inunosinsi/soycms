<?php
class SOYShop_MemberPage extends SOYShop_PageBase{

	private $currentUser;

	function getTitleFormatDescription(){
/**
    	$html = array();

    	$html[] = "商品名:%ITEM_NAME%";
    	$html[] = "商品コード:%ITEM_CODE%";
    	$html[] = "カテゴリ名:%CATEGORY_NAME%";

    	return implode(" ", $html);
**/
    }
    
    function getKeywordFormatDescription(){
/**
    	$html = array();
    	$html[] = "ショップ名:%SHOP_NAME%";
    	$html[] = "商品名:%ITEM_NAME%";
    	$html[] = "商品コード:%ITEM_CODE%";
    	$html[] = "カテゴリ名:%CATEGORY_NAME%";
    	return implode("<br />", $html);
**/
    }
    
    function getDescriptionFormatDescription(){
/**
    	$html = array();
    	$html[] = "ショップ名:%SHOP_NAME%";
    	$html[] = "商品名:%ITEM_NAME%";
    	$html[] = "商品コード:%ITEM_CODE%";
    	$html[] = "カテゴリ名:%CATEGORY_NAME%";
    	return implode("<br />", $html);
**/
    }

    function convertPageTitle($title){
/**
    	if($this->currentUser){
    		$title = str_replace("%ITEM_NAME%", $this->currentUser->getName(), $title);
    		$title = str_replace("%ITEM_CODE%", $this->currentUser->getCode(), $title);
    		return str_replace("%CATEGORY_NAME%", soyshop_get_category_name($this->currentUser->getCategory()), $title);
    	}
    	return $title;
**/
    }
    
    function getCurrentUser() {
    	return $this->currentUser;
    }
    function setCurrentUser($currentUser) {
    	$this->currentUser = $currentUser;
    }
}
?>