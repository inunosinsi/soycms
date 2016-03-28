<?php

class MoveUpAction extends SOY2Action{

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
    		
    		$trees = $page->getVirtual_tree();
    		
    		//おやを特定
    		$parent = null;
    		foreach($trees as $tree){
    			if(in_array($this->treeId,$tree->getChild())){
    				$parent = $tree;
    			}
    		}
    		
    		if(is_null($parent)) return SOY2Action::FAIELD;
    		
    		$childs = $parent->getChild();
    		
    		//存在しないページの削除
    		foreach($childs as $key => $child){
    			if(!isset($trees[$child])){
    				unset($childs[$key]);
    			}
    		}
    		//インデックスの付け直し
    		$childs = array_values($childs);
    		
    		$current_position = array_search($this->treeId,$childs);
    		
    		if($current_position == 0) return SOY2Action::FAILED;//すでに一番うえ
    		
    		
    		//swap
    		$childs[$current_position] = $childs[$current_position-1];
    		$childs[$current_position-1] = intval($this->treeId);
    		
    		
    		$parent->setChild($childs);
    		
    		$dao->updatePageConfig($page);
    	}catch(Exception $e){
    		return SOY2Action::FAILED;
    	}
    
    	return SOY2Action::SUCCESS;
    }
}
?>