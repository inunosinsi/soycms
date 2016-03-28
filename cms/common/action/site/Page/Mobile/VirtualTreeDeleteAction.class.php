<?php

class VirtualTreeDeleteAction extends SOY2Action{

	private $pageId;
	private $treeId;
	
	function getPageId() {
    	return $this->pageId;
    }
    function setPageId($pageId) {
    	$this->pageId = $pageId;
    }
    function getTreeId() {
    	return $this->treeId;
    }
    function setTreeId($treeId) {
    	$this->treeId = $treeId;
    }

    function execute() {
    
    	$dao = SOY2DAOFactory::create("cms.MobilePageDAO");
    	try{
    		$page = $dao->getById($this->pageId);
    		$id = $page->getId();
    		
    		$page->deleteVirtual_tree($this->treeId);
    		
    		$trees = $page->getVirtual_tree();
    		
    		foreach($trees as $key => $tree){
    			$trees[$key]->removeChild($this->treeId);
    		}
    		$page->setVirtual_tree($trees); 		
    		
    		
    		$dao->updatePageConfig($page);
    	}catch(Exception $e){
    		return SOY2Action::FAILED;
    	}
    
    	return SOY2Action::SUCCESS;
    }

    
}
?>