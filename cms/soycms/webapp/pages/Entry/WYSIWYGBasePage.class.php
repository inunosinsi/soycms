<?php

class WYSIWYGBasePage extends CMSWebPageBase{
	
	var $id;
	
    function WYSIWYGBasePage($args) {
    	
    	@$this->id = $args[0];
    	
    	WebPage::WebPage();
    	
    	$cssLists = $this->getCSSList();
    	
    	$link = "";
    	
    	$siteDir = get_site_directory(true);
    	foreach($cssLists as $id => $css){
    		$path = $siteDir . $css->getFilePath();
    		$link .= '<link rel="alternate stylesheet" href="'.$path.'" title="'.$css->getId().'"/>';
    	}
    	
    	$entryCssLists = $this->getEntryCSSList();
    	foreach($entryCssLists as $id => $css){
    		$link .= '<link rel="alternate stylesheet" href="'.$css["filePath"].'" title="'.$css["id"].'"/>';
    	}    	
    	
    	$element = SOY2HTMLElement::createHtmlElement($link);
    	$this->add("stylesheets",$element);
    	
    	
    }
    
    /**
     *  CSSのリストを返す
     */
    function getCSSList(){
    	$result = $this->run("CSS.ListAction");
    	if(!$result->success()){
    		return array();
    	}else{
    		$list = $result->getAttribute("list");
    		return $list;
    	}		
    }
    
    /**
     * 記事雛形のCSSのリストを返す
     */
    function getEntryCSSList(){
    	$result = $this->run("EntryTemplate.EntryCSSAction");
    	$css = $result->getAttribute("EntryCSS");
    	
    	$css["current_style"] = array(
			"id" => "current_style",
			"filePath" => SOY2PageController::createLink("Entry.CSS.".$this->id)
		);
    	
    	return $css;
    	    	    	
    }
}
?>