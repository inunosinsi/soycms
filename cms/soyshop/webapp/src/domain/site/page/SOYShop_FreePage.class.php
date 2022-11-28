<?php

class SOYShop_FreePage extends SOYShop_PageBase{

    private $title;

    private $content;

    private $updateDate;


    function getTitle() {
    	if(!is_string($this->title) || strlen($this->title) < 1){
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

    function getKeywordFormatDescription(){
    	return self::_getCommonFormat();
    }

    function getDescriptionFormatDescription(){
    	return self::_getCommonFormat();
    }

	function getTitleFormatDescription(){
		$tags = parent::getCommonFormat();
		$tags[] = array("label" => "コンテンツのタイトル", "format" => "%CONTENTS_TITLE%");

		$html = array();
		$html[] = "<table style=\"margin-top:5px;\">";
		foreach($tags as $tag){
			$html[] = "<tr><td>" . $tag["label"] . "：</td><td><strong>" . $tag["format"] . "</strong></td></tr>";
		}
		$html[] = "</table>";
    	return implode("\n", $html);
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

    function convertPageTitle(string $title){
		$title = parent::convertPageTitle($title);
    	return str_replace("%CONTENTS_TITLE%", $this->getTitle(), $title);
    }
}
