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
		$html[] = "<table style=\"margin-top:5px;\">";
    	$html[] = "<tr><td>ショップ名：</td><td><strong>%SHOP_NAME%</strong></td></tr>";
    	$html[] = "<tr><td>ページ名：</td><td><strong>%PAGE_NAME%</strong></td></tr>";
		$html[] = "<tr><td>コンテンツのタイトル：</td><td><strong>%CONTENTS_TITLE%</strong></td></tr>";
		$html[] = "</table>";
    	return implode("\n", $html);
    }

    function getKeywordFormatDescription(){
    	return parent::getCommonFormat();
    }

    function getDescriptionFormatDescription(){
    	return parent::getCommonFormat();
    }

    function convertPageTitle($title){
    	return str_replace("%CONTENTS_TITLE%", $this->getTitle(), $title);
    }
}
