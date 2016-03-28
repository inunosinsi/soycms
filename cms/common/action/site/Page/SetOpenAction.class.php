<?php

class SetOpenAction extends SOY2Action{

	private $pageIds = array();
	

    function execute($request,$form,$response) {
    	if(is_array($form->pageIds)){
    		$ids = array_merge($this->pageIds,$form->pageIds);	
    	}else{
    		$ids = $this->pageIds;
    	}
    	
    	$dao = SOY2DAOFactory::create("cms.PageDAO");
    	
    	$dao->begin();
    	try{
	    	foreach($ids as $id){
	    		$page = $dao->getById($id);
	    		$page->setIsPublished(1);
	    		$dao->update($page);
	    	}
	    	
	    	$dao->commit();
    	}catch(Exception $e){
    		$dao->rollback();
    		return SOY2Action::FAILED;
    	}
    	
    	return SOY2Action::SUCCESS;
    	
    }

    function getPageIds() {
    	return $this->pageIds;
    }
    function setPageIds($pageIds) {
    	$this->pageIds = $pageIds;
    }
}

class SetOpenActionForm extends SOY2ActionForm{
	var $pageIds = array();

	function getPageIds() {
		return $this->pageIds;
	}
	function setPageIds($pageIds) {
		if(!is_array($pageIds)){
			$this->pageIds = array();	
		}
		$this->pageIds = $pageIds;
		
	}
}

?>