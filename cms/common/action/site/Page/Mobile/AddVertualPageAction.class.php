<?php

class AddVertualPageAction extends SOY2Action{

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
    		
    		$tree = new VirtualTreePage();
    		$id = $page->insertVirtual_tree($tree,$this->treeId);
    		
    		if($id>0){
    			$this->setAttribute("id",$id);
    		}
    		
    		$dao->updatePageConfig($page);
    	}catch(Exception $e){
    		return SOY2Action::FAILED;
    	}    
    	return SOY2Action::SUCCESS;
    }
}
?>