<?php

class SelectAction extends SOY2Action{

    function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response){
    	SOY2::import("domain.cms.Block");
    	
    	$blockList = Block::getBlockComponentList();
    	
    	//表示順変更
    	//uksort($blockList,create_function('$key1,$key2','if($key1[0] == "H")return 1;return -1;'));
    	
    	$this->setAttribute("blockList",$blockList);
    	$this->setAttribute("pageId",$form->pageId);
    	$this->setAttribute("soyId",$form->soyId);
    }
}

class SelectActionForm extends SOY2ActionForm{
	var $soyId;
	var $pageId;
	
	function setSoyId($soyId){
		$this->soyId = $soyId;
	}
	
	function setPageId($pageId){
		$this->pageId = $pageId;
	}
}
?>