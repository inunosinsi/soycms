<?php

class ConfirmStage extends StageBase{

    function ConfirmStage() {
    	
    }
        
    function execute(){
    	parent::__construct();
    	$page = $this->run("Page.DetailAction",array("id"=>$this->wizardObj->pageId))->getAttribute("Page");
    	$this->createAdd("page_link","HTMLLink",array(
    		"link"=>UserInfoUtil::getSiteURL().$page->getUri(),
    		"text"=>UserInfoUtil::getSiteURL().$page->getUri()
    	));	
    }
    
    function checkNext(){
    	return true;
    }
    
    function checkBack(){
    	return true;
    }
    
    function getNextObject(){
    	return "EndStage";
    }
    
    
    function getNextString(){
    	return "終了";
    }
    
    function getBackString(){
    	return "";
    }
}
?>