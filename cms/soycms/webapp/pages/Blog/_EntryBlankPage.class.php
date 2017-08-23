<?php

class _EntryBlankPage extends CMSWebPageBase{

    
    
    private $pageId;
	
    function __construct(){
    	parent::__construct();
    	
    }
    
    function execute(){
    	$this->createAdd("entry_create_link","HTMLLink",array(
    		"link"=>SOY2PageController::createLink("Blog.Entry.".$this->pageId)
    	));
    }

    function getPageId() {
    	return $this->pageId;
    }
    function setPageId($pageId) {
    	$this->pageId = $pageId;
    }
}
?>