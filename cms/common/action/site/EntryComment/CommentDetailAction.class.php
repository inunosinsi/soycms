<?php

class CommentDetailAction extends SOY2Action{

	private $commentId;

    function execute() {
    	$dao = SOY2DAOFactory::create("cms.EntryCommentDAO");
    	
    	try{
    		$comment = $dao->getById($this->commentId);
    	}catch(Exception $e){
    		return SOY2Action::FAILED;
    	}
    	
    	$this->setAttribute("entity",$comment);
    	
    	return SOY2Action::SUCCESS;
    	
    }

    function getCommentId() {
    	return $this->commentId;
    }
    function setCommentId($commentId) {
    	$this->commentId = $commentId;
    }
}
?>