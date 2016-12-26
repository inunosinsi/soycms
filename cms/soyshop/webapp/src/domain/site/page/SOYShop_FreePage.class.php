<?php

class SOYShop_FreePage extends SOYShop_PageBase{

    private $title;

    private $content;

    private $updateDate;


    function getTitle() {
    	if(strlen($this->title) < 1){
    		return "[無題]";
    	}
    	return $this->title;
    }
    function setTitle($title) {
    	$this->title = $title;
    }
    function getContent() {
    	return $this->content;
    }
    function setContent($content) {
    	$this->content = $content;
    }
    function getUpdateDate() {
    	return $this->updateDate;
    }
    function setUpdateDate($updateDate) {
    	$this->updateDate = $updateDate;
    }

    function getUpdateDateText(){
    	if(is_null($this->updateDate)){
    		return "-";
    	}

    	return date("Y-m-d H:i:s", $this->updateDate);
    }

    /* method */

    function getTitleFormatDescription(){
    	$html = array();

    	$html[] = "コンテンツのタイトル:%CONTENTS_TITLE%";

    	return implode(" ", $html);
    }
    
    function getKeywordFormatDescription(){
    	$html = array();
    	$html[] = "ショップ名:%SHOP_NAME%";
    	$html[] = "ページ名:%PAGE_NAME%";
    	return implode("<br />", $html);
    }
    
    function getDescriptionFormatDescription(){
    	$html = array();
    	$html[] = "ショップ名:%SHOP_NAME%";
    	$html[] = "ページ名:%PAGE_NAME%";
    	return implode("<br />", $html);
    }

    function convertPageTitle($title){
    	return str_replace("%CONTENTS_TITLE%", $this->getTitle(), $title);
    }
}
?>