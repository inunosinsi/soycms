<?php

class EntryListAction extends SOY2Action{

	private $pageId;
	
	function setPageId($pageId){
		$this->pageId = $pageId;
	}

    function execute() {
    	$dao = SOY2DAOFactory::create("cms.BlogPageDAO");
    	$blog = $dao->getById($this->pageId);
    	$categoryLabels = array($blog->getBlogLabelId());
    	
    	$logic = SOY2Logic::createInstance("logic.site.Entry.EntryLogic");
    	$entries = $logic->getOpenEntryByLabelIds($categoryLabels);
    	$this->setAttribute("entries",$entries);
    	return SOY2Action::SUCCESS;
    }
}
?>