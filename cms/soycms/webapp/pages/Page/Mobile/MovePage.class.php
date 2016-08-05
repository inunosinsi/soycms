<?php

class MovePage extends CMSWebPageBase{

    function __construct($arg) {
    	if(soy2_check_token()){
	    	$pageId = @$arg[0];
	    	$treeId = @$arg[1];
	    	$targeNodeId = @$arg[2];
	    	$this->run("Page.Mobile.MoveAction",array(
	    		"pageId"=>$pageId,
	    		"treeId"=>$treeId,
	    		"targetNodeId"=>$targeNodeId
	    	));
    	}
    	
    	exit;
    }
}
?>