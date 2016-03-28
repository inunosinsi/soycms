<?php

class MoveAction extends SOY2Action{

	private $pageId;
	private $treeId;
	private $targetNodeId;
	
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
    function setTargetNodeId($nodeId){
    	$this->targetNodeId = $nodeId;
    }
    function getTargetNodeId(){
    	return $this->targetNodeId;
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
    		
    		//追加先ノードを取得
    		$targetNode = $page->getVirtualTreeById($this->targetNodeId);
    		
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
    		
    		//現在のノードを削除
    		unset($childs[$current_position]);
    		$parent->setChild($childs);
    		
    		//ターゲットノードに追加
    		$targetchilds = $targetNode->getChild();
    		
    		//存在しないページの削除
    		foreach($targetchilds as $key => $child){
    			if(!isset($trees[$child])){
    				unset($childs[$key]);
    			}
    		}
    		//インデックスの付け直し
    		$targetchilds = array_values($targetchilds);
    		
    		//ノード追加
    		$targetchilds[] = intval($this->treeId);
    		
    		$targetNode->setChild($targetchilds);
    		
    		$moved_tree = $page->getVirtualTreeById($this->treeId);
    		$moved_tree->setParent($this->targetNodeId);
    		$page->updateVirtual_tree($moved_tree);
    		
    		$dao->updatePageConfig($page);
    	}catch(Exception $e){
    		
    		return SOY2Action::FAILED;
    	}
    
    	return SOY2Action::SUCCESS;
    }
}
?>