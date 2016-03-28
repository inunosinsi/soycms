<?php

class TrackbackListAction extends SOY2Action{

	private $pageId;
	private $offset;
	private $limit;
	
	function setPageId($pageId){
		$this->pageId = $pageId;
	}
	
	function setLimit($limit){
		$this->limit = $limit;
	}
	
	function setOffset($offset){
		$this->offset = $offset;
	}
	
    function execute() {
    	$labels = $this->getLabelsByPageId($this->pageId);
    	
    	if(is_null($labels)){
    		return SOY2Action::FAILED;
    	}
    	
    	
    	$logic = SOY2Logic::createInstance("logic.site.Entry.EntryTrackbackLogic");
    	$this->setAttribute("list",$logic->getByLabelIds($labels,$this->limit,$this->offset));
    	$this->setAttribute("count",$logic->getTotalCount());
    	
    	return SOY2Action::SUCCESS;
    
    }
    
    function getLabelsByPageId($pageId){
    	try{
    		$pageDAO = SOY2DAOFactory::create("cms.BlogPageDAO");
    		return array($pageDAO->getById($pageId)->getBlogLabelId());	
    	}catch(Exception $e){
    		return null;
    	}
    	
    	
    }
}
?>