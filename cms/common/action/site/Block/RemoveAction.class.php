<?php

class RemoveAction extends SOY2Action{

	private $id;

	function setId($id){
		$this->id = $id;
	}

    function execute(){
    	$logic = SOY2Logic::createInstance("logic.site.Block.BlockLogic");
    	$pageId = $logic->getById($this->id)->getPageId();
    	
    	$logic->delete($this->id);
    	
    	$this->setAttribute("pageId",$pageId);
    	return SOY2Action::SUCCESS;
    	
    }
}
?>