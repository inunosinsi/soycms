<?php
/**
 * 
 */
class MobileUpdateAction extends SOY2Action{

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
    
    function execute($request,$form,$response) {
    	$dao = SOY2DAOFactory::create("cms.MobilePageDAO");
    	
    	try{
    		$page = $dao->getById($this->pageId);
    		
    		$tree = $page->getVirtualTreeById($this->treeId);
    		
    		$tree = SOY2::cast($tree,$form);
    		
    		$page->updateVirtual_tree($tree);
    		
    		$dao->updatePageConfig($page);
    	}catch(Exception $e){
    		return SOY2Action::FAILED;
    	}
    
    	return SOY2Action::SUCCESS;
    	
    }
}

class MobileUpdateActionForm extends SOY2ActionForm{
	var $title;
	var $type;
	var $size;
	var $entries;
	var $label;
	var $alias;

	function getTitle() {
		return $this->title;
	}
	function setTitle($title) {
		$this->title = $title;
	}
	function getType() {
		return $this->type;
	}
	function setType($type) {
		$this->type = $type;
	}
	function getSize() {
		return $this->size;
	}
	function setSize($size) {
		$this->size = $size;
	}

	function getEntries() {
		return $this->entries;
	}
	function setEntries($entries) {
		$this->entries = $entries;
	}

	function getLabel() {
		return $this->label;
	}
	function setLabel($label) {
		$this->label = $label;
	}
	function setAlias($alias){
		$alias = preg_replace('/^\/+/','',$alias);
		$alias = preg_replace('/^[0-9]+$/','',$alias);
		$alias = preg_replace('/\/+[\d]*$/','',$alias);
		$this->alias = $alias;
	}
}

?>