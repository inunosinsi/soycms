<?php

class ChangeDefaultsAction extends SOY2Action{
	
	private $pageId;
	
	function execute($request,$form,$response){
		$dao = SOY2DAOFactory::create("cms.BlogPageDAO");
		try{
    		$page = $dao->getById($this->pageId);
		}catch(Exception $e){
			return SOY2Action::FAILED;
		}
		
		$page->setDefaultAcceptComment($form->default_accept);
		
		try{
    		$dao->updatePageConfig($page);
    		return SOY2Action::SUCCESS;
		}catch(Exception $e){
			return SOY2Action::FAILED;
		}
	}
	
	

	function getPageId() {
		return $this->pageId;
	}
	function setPageId($pageId) {
		$this->pageId = $pageId;
	}
}

class ChangeDefaultsActionForm extends SOY2ActionForm{
	var $default_accept;

	function getDefault_accept() {
		return $this->default_accept;
	}
	function setDefault_accept($default_accept) {
		$this->default_accept = $default_accept;
	}
}
?>
